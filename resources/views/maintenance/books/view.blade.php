@extends('layouts.admin-app')
@section('content')
<div class="container mx-auto px-4 sm:px-6 lg:px-8">
  <h1 class="text-3xl text-center font-bold text-gray-800 dark:text-white mt-8 mb-6">Maintenance</h1>
  <div class="w-full p-4 sm:p-6 bg-white border border-gray-200 rounded-lg dark:bg-gray-800 dark:border-gray-700 shadow-md">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4">
      <h5 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Material Details</h5>
      <div class="flex items-center gap-2 mt-4 sm:mt-0">
        <a href="{{ route('maintenance.edit-book', ['id' => $book->id, 'return_to' => request()->fullUrl()]) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-4 focus:outline-none focus:ring-blue-300 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">
          <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m14.304 4.844 2.852 2.852M7 7H4a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h11a1 1 0 0 0 1-1v-4.5m2.409-9.91a2.017 2.017 0 0 1 0 2.853l-6.844 6.844L8 14l.713-3.565 6.844-6.844a2.015 2.015 0 0 1 2.852 0Z"/>
          </svg>
          Edit
        </a>
        <a href="{{ request('return_to', route('maintenance.books')) }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-center text-white bg-primary-500 rounded-lg hover:bg-primary-400 focus:ring-4 focus:outline-none focus:ring-primary-400 dark:bg-primary-400 dark:hover:bg-primary-500 dark:focus:ring-primary-500">
          <svg class="w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5H1m0 0 4 4M1 5l4-4" />
          </svg>
          Back
        </a>
      </div>
    </div>
    <hr class="h-px my-3 bg-gray-200 border-0 dark:bg-gray-700">

    <div class="space-y-8 mt-6">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Left Column: Cover & Barcode --}}
        <div class="lg:col-span-1 space-y-6">
          <!-- Cover Image Card -->
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
              <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
              <h6 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Material Cover</h6>
            </div>
            <div class="p-5 flex justify-center">
              @if(!empty($book->cover_image))
                <img class="object-contain w-full max-w-xs rounded-lg shadow-md" src="data:{{ $mimeType }};base64, {{ $book->cover_image }}" alt="Material Image">
              @elseif(!empty($cover))
                <img class="object-contain w-full max-w-xs rounded-lg shadow-md" src="{{ $cover }}" alt="Material Image">
              @else
                <div class="w-full max-w-xs relative aspect-[3/4] bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center overflow-hidden">
                  <img class="object-contain w-full h-full dark:hidden opacity-50" src="{{ asset('img/Book-light.png') }}" alt="Material Image">
                  <img class="hidden object-contain w-full h-full dark:block opacity-50" src="{{ asset('img/Book-dark.png') }}" alt="Book Image">
                  <span class="absolute text-gray-400 dark:text-gray-500 font-medium">No Cover Available</span>
                </div>
              @endif
            </div>
          </div>

          <!-- Barcode Card -->
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
              <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
              <h6 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Barcode Information</h6>
            </div>
            <div class="p-5 flex flex-col items-center gap-3 text-center">
              <div class="bg-white p-4 rounded-lg border border-gray-100 shadow-sm">
                <img src="data:image/jpeg;base64, {{ $book->barcode }}" alt="Material Barcode" class="max-w-full">
              </div>
              <p class="text-lg font-mono font-bold text-gray-800 dark:text-gray-200">{{ $book->accession }}</p>
            </div>
          </div>
        </div>

        {{-- Right Column: Detailed Information --}}
        <div class="lg:col-span-2 space-y-8">
          <!-- Section 1: Basic Information -->
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
              <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
              <h6 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Basic Information</h6>
            </div>
            <div class="p-6">
              <h5 class="text-2xl font-extrabold text-gray-900 dark:text-white mb-2 break-words leading-tight">{{ $book->title }}</h5>
              @if(!empty($book->parallel_title))
                <div class="flex flex-wrap gap-2 mb-6">
                  @foreach(explode(';', $book->parallel_title) as $pTitle)
                    @if(trim($pTitle) !== '')
                      <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-semibold bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200 dark:border-blue-800">
                        {{ trim($pTitle) }}
                      </span>
                    @endif
                  @endforeach
                </div>
              @else
                <div class="mb-6"></div>
              @endif
              <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
                <div class="flex flex-col">
                  <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Accession Number</span>
                  <span class="text-base font-medium text-gray-900 dark:text-white">{{ $book->accession }}</span>
                </div>
                <div class="flex flex-col">
                  <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Call Number</span>
                  <span class="text-base font-medium text-gray-900 dark:text-white">{{ $book->call_number ?? '-' }}</span>
                </div>
                <div class="flex flex-col">
                  <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Material Type</span>
                  <span class="text-base font-medium text-gray-900 dark:text-white">{{ $book->book_type }}</span>
                </div>
                <div class="flex flex-col">
                  <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</span>
                  <span class="text-base font-medium text-gray-900 dark:text-white">{{ $book->category->name ?? '-' }}</span>
                </div>
                <div class="flex flex-col">
                  <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Subject Access Codes</span>
                  <div class="flex flex-wrap gap-2 mt-1">
                    @forelse($book->subjectAccessCodes as $code)
                      <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 rounded text-sm border border-gray-200 dark:border-gray-600">
                        {{ $code->access_code }}
                      </span>
                    @empty
                      <span class="text-base font-medium text-gray-900 dark:text-white">-</span>
                    @endforelse
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Section 2: Authors & Contributors -->
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
              <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
              <h6 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Authors & Contributors</h6>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if(is_array($book->authors))
                  @php $hasAuthors = false; @endphp
                  @foreach(['Main author', 'Corporate author', 'Added authors', 'Contributors'] as $key)
                    @if(!empty($book->authors[$key]))
                      @php $hasAuthors = true; @endphp
                      <div class="flex flex-col">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $key }}</span>
                        <span class="text-base font-medium text-gray-900 dark:text-white">{{ $book->authors[$key] }}</span>
                      </div>
                    @endif
                  @endforeach
                  @if(!$hasAuthors)
                    <p class="text-gray-500 italic">No author information recorded.</p>
                  @endif
                @else
                  <div class="flex flex-col md:col-span-2">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Authors</span>
                    <span class="text-base font-medium text-gray-900 dark:text-white">{{ $book->authors ?? '-' }}</span>
                  </div>
                @endif
              </div>
            </div>
          </div>

          @if($book->book_type != 'Non-print')
          <!-- Section 3: Classification -->
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
              <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
              <h6 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Classification</h6>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
                <div class="flex flex-col">
                  <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">ISBN</span>
                  <span class="text-base font-medium text-gray-900 dark:text-white">{{ $book->isbn ?? '-' }}</span>
                </div>
                <div class="flex flex-col">
                  <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Edition</span>
                  <span class="text-base font-medium text-gray-900 dark:text-white">{{ $book->edition ?? '-' }}</span>
                </div>
              </div>
            </div>
          </div>
          @endif

          <!-- Section 4: Material Description -->
          @if($book->book_type != 'Non-print')
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
              <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
              <h6 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Material Description</h6>
            </div>
            <div class="p-6 space-y-6">
              @if(is_array($book->description))
                @php $hasDesc = false; @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  @if(!empty($book->description['Description']))
                    @php $hasDesc = true; @endphp
                    <div class="flex flex-col md:col-span-2">
                      <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Physical Description</span>
                      <span class="text-base text-gray-900 dark:text-white break-words">{{ $book->description['Description'] }}</span>
                    </div>
                  @endif
                  @foreach(['Extent', 'Acc Material', 'Series'] as $key)
                    @if(!empty($book->description[$key]))
                      @php $hasDesc = true; @endphp
                      <div class="flex flex-col">
                        <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $key }}</span>
                        <span class="text-base font-medium text-gray-900 dark:text-white">{{ $book->description[$key] }}</span>
                      </div>
                    @endif
                  @endforeach
                  @foreach(['Content notes', 'Abstract', 'Reviews'] as $key)
                    @if(!empty($book->description[$key]))
                      @php $hasDesc = true; @endphp
                      <div class="flex flex-col md:col-span-2 bg-gray-50 dark:bg-gray-700/30 p-4 rounded-lg border border-gray-100 dark:border-gray-700/50">
                        <span class="text-xs font-bold text-primary-600 dark:text-primary-400 uppercase tracking-widest mb-2">{{ $key }}</span>
                        <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">{{ $book->description[$key] }}</p>
                      </div>
                    @endif
                  @endforeach
                </div>
                @if(!$hasDesc)
                  <p class="text-gray-500 italic">No detailed description available.</p>
                @endif
              @else
                <div class="flex flex-col">
                  <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Description</span>
                  <span class="text-base text-gray-900 dark:text-white break-words">{{ $book->description ?? '-' }}</span>
                </div>
              @endif
            </div>
          </div>
          @endif

          <!-- Section 5: Publishing & Logistics -->
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
              <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
              <h6 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Publishing & Logistics</h6>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-y-4 gap-x-8">
                <div class="flex flex-col">
                  <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Publisher</span>
                  <span class="text-base font-medium text-gray-900 dark:text-white">{{ $book->publisher ?? '-' }}</span>
                </div>
                <div class="flex flex-col">
                  <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Copyright Year</span>
                  <span class="text-base font-medium text-gray-900 dark:text-white">{{ $book->copyrights ?? '-' }}</span>
                </div>
                @if($book->book_type != 'Non-print')
                <div class="flex flex-col">
                  <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Place of Publication</span>
                  <span class="text-base font-medium text-gray-900 dark:text-white">{{ $book->place_of_publication ?? '-' }}</span>
                </div>
                @endif
                <div class="flex flex-col">
                  <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Location</span>
                  <span class="text-base font-medium text-gray-900 dark:text-white">{{ $book->location ?? '-' }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Section 6: Status & Digital Assets -->
          <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm overflow-hidden">
            <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/50 border-b border-gray-200 dark:border-gray-700 flex items-center gap-2">
              <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
              <h6 class="text-sm font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Status & Media</h6>
            </div>
            <div class="p-6">
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="flex flex-col">
                  <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Remarks</span>
                  <span class="text-base font-medium text-gray-900 dark:text-white">{{ $book->remarks }}</span>
                </div>
                <div class="flex flex-col">
                  <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Availability</span>
                  <div class="mt-1">
                    @if($book->availability_status == 'Available')
                      <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 border border-green-200 dark:border-green-800">Available</span>
                    @else
                      <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400 border border-red-200 dark:border-red-800">Unavailable</span>
                    @endif
                  </div>
                </div>
                <div class="flex flex-col">
                  <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Condition</span>
                  <span class="text-base font-medium text-gray-900 dark:text-white">{{ $book->condition_status }}</span>
                </div>
                @if(!empty($book->digital_copy_url))
                <div class="flex flex-col md:col-span-2 lg:col-span-3">
                  <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Digital Copy</span>
                  <a href="{{ $book->digital_copy_url }}" target="_blank" class="text-primary-600 dark:text-primary-400 hover:underline inline-flex items-center gap-1 mt-1 font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    Access Digital Resource
                  </a>
                </div>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection