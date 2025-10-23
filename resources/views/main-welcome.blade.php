@extends('layouts.welcome-layout')
@section('content')
<div class="max-w-screen-xl mx-auto px-4 sm:px-6 md:px-10 my-8 md:my-16 lg:my-20">
  <div class="flex flex-col md:flex-row items-center justify-between gap-8 md:gap-10 lg:gap-12">
    <img
      class="order-1 rounded-full w-32 h-32 sm:w-40 sm:h-40 md:w-56 md:h-56 lg:w-72 lg:h-72 object-cover"
      src="{{ asset('img/BPSLogo.png') }}"
      alt="Bicutan Parochial School Logo">
    <div class="order-2 flex flex-col items-center text-center">
      <h1 class="text-xl sm:text-2xl md:text-2xl lg:text-3xl font-semibold text-black dark:text-white">
        Bicutan Parochial School
      </h1>
      <hr class="w-full max-w-xs h-px bg-gray-500 border-0 my-2 dark:bg-white">
      <h2 class="text-lg sm:text-xl md:text-xl lg:text-2xl font-semibold text-black dark:text-white">
        Library Management System
      </h2>
      <h4 class="text-base sm:text-lg md:text-base lg:text-lg font-semibold text-black my-4 dark:text-white">
        Developed by
      </h4>
      <h3 class="text-xl sm:text-2xl md:text-2xl lg:text-3xl font-semibold text-black dark:text-white">
        OwlQuery Group
      </h3>
    </div>
    <div class="order-3">
      <img
        class="block dark:hidden w-40 sm:w-48 md:w-56 lg:w-60 h-auto"
        src="{{ asset('img/OwlQuery.png') }}"
        alt="OwlQuery">
      <img
        class="hidden dark:block w-40 sm:w-48 md:w-56 lg:w-60 h-auto"
        src="{{ asset('img/OwlQuery Dark.png') }}"
        alt="OwlQuery (Dark)">
    </div>
  </div>
</div>
@endsection