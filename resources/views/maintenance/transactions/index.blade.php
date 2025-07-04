@extends('layouts.admin-app')
@section('content')
@use('App\Enum\PermissionsEnum')
<h1 class="font-semibold text-center text-4xl p-5">Maintenance</h1>
<div class="w-full p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
  <div class="flex justify-between">
    <h5 class="mb-1 text-2xl font-bold tracking-tight text-gray-900 dark:text-white">Transactions</h5>
  </div>
  <hr class="h-px my-3 bg-gray-200 border-0">
  @include('maintenance.transactions.table')
</div>
@endsection