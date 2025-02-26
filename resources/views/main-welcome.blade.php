@extends('layouts.welcome-layout')
@section('content')
<div class="flex justify-between items-center my-20">
  <img class="rounded-full w-96 h-96" src="{{ asset('img/School Logo.png') }}" alt="School Logo">
  <div class="flex flex-col justify-evenly">
    <h1 class="lg:text-2xl md:text-lg text-black font-semibold text-center dark:text-white">Bicutan Parochial School</h1>
    <hr class="h-px bg-gray-500 border-0 my-2 dark:bg-white">
    <h1 class="lg:text-xl md:text-md text-black font-semibold text-center dark:text-white">Library Management System</h1>
    <h4 class="lg:text-lg md:text-sm text-black font-semibold text-center my-4 dark:text-white">Developed by</h4>
    <h1 class="lg:text-2xl md:text-lg text-black font-semibold text-center dark:text-white">OwlQuery Group</h1>
  </div>
  <img class="rounded-full w-96 h-96 block dark:hidden" src="{{ asset('img/OwlQuery.png') }}" alt="OwlQuery">
  <img class="rounded-full w-96 h-96 hidden dark:block" src="{{ asset('img/OwlQuery Dark.png') }}" alt="OwlQuery">
</div>
@endsection