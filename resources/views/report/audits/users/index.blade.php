@extends('layouts.admin-app')
@section('content')
@use('App\Enum\PermissionsEnum')
<h1 class="font-semibold text-center text-4xl p-5">Report Document For Audits</h1>
@include('report.audits.users.table')
@endsection