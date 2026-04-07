@extends('layouts.admin-app')

@section('content')
<div class="w-full max-w-6xl px-4 py-8">
  <div class="mb-8">
    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Mail Preview Tester</h1>
    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
      Open any preview below to inspect the corresponding mail Blade in the browser using sample data.
    </p>
  </div>

  <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
    @foreach($previews as $preview)
      <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800">
        <div class="mb-3">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $preview['label'] }}</h2>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ $preview['description'] }}</p>
        </div>

        <div class="flex items-center justify-between border-t border-gray-100 pt-4 dark:border-gray-700">
          <span class="text-xs font-mono text-gray-500 dark:text-gray-400">{{ $preview['slug'] }}</span>
          <a
            href="{{ $preview['url'] }}"
            target="_blank"
            rel="noopener noreferrer"
            class="rounded-lg bg-primary-500 px-4 py-2 text-sm font-medium text-white transition hover:bg-primary-400"
          >
            Open Preview
          </a>
        </div>
      </div>
    @endforeach
  </div>
</div>
@endsection
