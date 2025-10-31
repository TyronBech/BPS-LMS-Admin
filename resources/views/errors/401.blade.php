@extends('errors.layout')

@section('code', '401')
@section('message', __('Unauthorized'))
@section('description', __('You don\'t have the right key for this section of the library. Please log in or check your permissions.'))

@section('actions')
<a class="btn primary" href="{{ route('login') }}">Login</a>
<button type="button" class="btn" onclick="history.back()">Go back</button>
@endsection