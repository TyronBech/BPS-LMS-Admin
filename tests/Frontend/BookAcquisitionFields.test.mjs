import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import { runInNewContext } from 'node:vm';

const source = readFileSync(new URL('../../resources/views/maintenance/books/partials/acquisition-script.blade.php', import.meta.url), 'utf8')
    .replace('<script>', '').replace('</script>', '');

// A minimal DOM contract for the shared script; layout/HTML is covered by the PHP view tests.
function mount({ research = false, copyResearch = false, restoreCopy = false } = {}) {
    const category = new EventTarget();
    category.selectedOptions = [{ dataset: { acquisitionEligible: String(!research) } }];
    const bookType = new EventTarget();
    const copyCategory = { dataset: { acquisitionEligible: String(!copyResearch) } };
    const section = (id) => {
        const classes = new Set();
        return {
            dataset: { categoryInput: id },
            hidden: false,
            disabled: false,
            classList: { toggle(name, force) { force ? classes.add(name) : classes.delete(name); }, contains: (name) => classes.has(name) },
        };
    };
    const main = section('category');
    const copy = section('copy_category');
    let opened = 0;
    const elements = {
        category, book_type: bookType, copy_category: copyCategory,
        'copy-book-modal': { dataset: { restoreCopy: String(restoreCopy) } },
    };
    const document = new EventTarget();
    document.getElementById = (id) => elements[id];
    document.querySelectorAll = () => [main, copy];
    document.querySelector = () => ({ click() { opened++; } });
    const window = new EventTarget();
    runInNewContext(source, { document, window, queueMicrotask });
    document.dispatchEvent(new Event('DOMContentLoaded'));
    return { category, bookType, main, copy, window, opened: () => opened };
}

test('research acquisition controls are hidden and disabled on load', () => {
    const { main } = mount({ research: true });
    assert.equal(main.hidden, true);
    assert.equal(main.disabled, true);
    assert.equal(main.classList.contains('hidden'), true);
});

test('category changes re-enable eligible controls and disable research controls', async () => {
    const { category, main } = mount({ research: true });
    category.selectedOptions[0].dataset.acquisitionEligible = 'true';
    category.dispatchEvent(new Event('change'));
    await new Promise(queueMicrotask);
    assert.equal(main.hidden, false);
    assert.equal(main.disabled, false);
    category.selectedOptions[0].dataset.acquisitionEligible = 'false';
    category.dispatchEvent(new Event('change'));
    await new Promise(queueMicrotask);
    assert.equal(main.hidden, true);
    assert.equal(main.disabled, true);
});

test('type changes use the category selected by the existing form handler', async () => {
    const { category, bookType, main } = mount();
    bookType.addEventListener('change', () => {
        category.selectedOptions[0].dataset.acquisitionEligible = 'false';
    });
    bookType.dispatchEvent(new Event('change'));
    await new Promise(queueMicrotask);
    assert.equal(main.disabled, true);
});

test('copy eligibility follows the source category independently of unsaved main category changes', async () => {
    const { category, main, copy } = mount({ copyResearch: true });
    category.selectedOptions[0].dataset.acquisitionEligible = 'true';
    category.dispatchEvent(new Event('change'));
    await new Promise(queueMicrotask);
    assert.equal(main.disabled, false);
    assert.equal(copy.disabled, true);
});

test('only a failed copy submission reopens its dialog after load', () => {
    const retry = mount({ restoreCopy: true });
    assert.equal(retry.opened(), 0);
    retry.window.dispatchEvent(new Event('load'));
    assert.equal(retry.opened(), 1);
    const clean = mount();
    clean.window.dispatchEvent(new Event('load'));
    assert.equal(clean.opened(), 0);
});
