@extends('layouts.admin-app')
@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Maintenance</h1>
  <div class="w-full p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Announcement Details</h5>
      <a href="{{ route('maintenance.library-website.announcements') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-primary-500 rounded-lg hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500 mt-4 sm:mt-0">
        <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5H1m0 0 4 4M1 5l4-4" />
        </svg>
        Back
      </a>
    </div>
    <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-700">

    <div class="max-w-4xl mx-auto">
      {{-- Image --}}
      @if($announcement->image)
      <div class="mb-6">
        <img src="data:image/jpeg;base64,{{ $announcement->image }}" alt="{{ $announcement->title }}" class="w-full max-h-80 object-cover rounded-lg border border-gray-200 dark:border-gray-700">
      </div>
      @endif

      {{-- Badges --}}
      <div class="flex flex-wrap gap-2 mb-4">
        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">
          {{ $announcement->category }}
        </span>
        @if($announcement->priority === 'high')
          <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">High Priority</span>
        @else
          <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">Normal Priority</span>
        @endif
        @if($announcement->is_featured)
          <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-yellow-900 dark:text-yellow-300">⭐ Featured</span>
        @endif
        @if($announcement->is_published)
          <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-green-900 dark:text-green-300">Published</span>
        @else
          <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded dark:bg-yellow-900 dark:text-yellow-300">Draft</span>
        @endif
      </div>

      {{-- Title --}}
      <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">{{ $announcement->title }}</h2>

      {{-- Date --}}
      @if($announcement->date)
      <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
        📅 {{ \Carbon\Carbon::parse($announcement->date)->format('F d, Y') }}
      </p>
      @endif

      {{-- Quote --}}
      @if($announcement->quote)
      <blockquote class="p-4 my-4 border-s-4 border-primary-500 bg-gray-50 dark:border-primary-500 dark:bg-gray-800 rounded-lg">
        <p class="text-xl italic font-medium leading-relaxed text-gray-900 dark:text-white">{{ $announcement->quote }}</p>
      </blockquote>
      @endif

      {{-- Content --}}
      <div class="mt-4 text-gray-700 dark:text-gray-300 whitespace-pre-wrap leading-relaxed">
        {!! nl2br(e($announcement->content)) !!}
      </div>

      <hr class="h-px my-6 bg-gray-200 border-0 dark:bg-gray-700">

      <div class="text-xs text-gray-400">
        Created: {{ $announcement->created_at->format('M d, Y h:i A') }}
        &bull; Last updated: {{ $announcement->updated_at->format('M d, Y h:i A') }}
        &bull; Slug: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">{{ $announcement->slug }}</code>
      </div>
    </div>
  </div>
</div>
@endsection
