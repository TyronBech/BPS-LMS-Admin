@extends('errors.layout')

@section('code', '403')
@section('message', __('Forbidden'))
@section('description', __('You don\'t have permission to access this page. It seems you\'ve wandered into a restricted area of the library.'))

@section('actions')
<a class="btn primary" href="{{ url('/') }}">Go to Home</a>
<button type="button" class="btn" onclick="history.back()">Go back</button>
@endsection