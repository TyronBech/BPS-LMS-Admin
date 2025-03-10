@extends('layouts.admin-app')
@section('content')
<h1 class="font-semibold text-center text-4xl p-5">Summary of Matrix</h1>
@include('report.categories.table')
@endsection