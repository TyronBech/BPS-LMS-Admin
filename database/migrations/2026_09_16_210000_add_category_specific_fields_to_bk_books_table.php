<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add category-specific fields for Periodical, Serials, and Academic Research.
     *
     * All columns are nullable so existing records remain unaffected.
     */
    public function up(): void
    {
        Schema::table('bk_books', function (Blueprint $table) {
            // ── Periodical fields ───────────────────────────────────────────
            $table->string('periodical_kind', 100)->nullable()->after('languages');
            $table->string('volume', 50)->nullable()->after('periodical_kind');
            $table->string('issue_number', 50)->nullable()->after('volume');
            $table->string('material_type', 100)->nullable()->after('issue_number');

            // ── Serials fields ──────────────────────────────────────────────
            $table->string('issn', 50)->nullable()->after('material_type');
            $table->string('frequency', 100)->nullable()->after('issn');
            $table->string('latest_received', 255)->nullable()->after('frequency');
            $table->text('notes')->nullable()->after('latest_received');
            $table->text('topical_access_point')->nullable()->after('notes');
            $table->text('corporate_access_point')->nullable()->after('topical_access_point');
            $table->string('discipline', 255)->nullable()->after('corporate_access_point');

            // ── Academic Research fields ────────────────────────────────────
            $table->string('institution', 255)->nullable()->after('discipline');
            $table->string('program', 255)->nullable()->after('institution');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bk_books', function (Blueprint $table) {
            $table->dropColumn([
                'periodical_kind',
                'volume',
                'issue_number',
                'material_type',
                'issn',
                'frequency',
                'latest_received',
                'notes',
                'topical_access_point',
                'corporate_access_point',
                'discipline',
                'institution',
                'program',
            ]);
        });
    }
};
