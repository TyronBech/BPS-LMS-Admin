@extends('errors.layout')

@section('code', '500')
@section('message', __('Server Error'))
@section('description', __('It seems a shelf has collapsed in our digital library. We\'ve dispatched our librarians to sort out the mess. Please try again in a little while.'))

@section('actions')
<a class="btn primary" href="{{ url('/') }}">Go to Home</a>
<button type="button" class="btn" onclick="history.back()">Go back</button>
@endsection