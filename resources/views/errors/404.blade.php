@extends('errors.layout')

@section('code', '404')
@section('message', __('Not Found'))
@section('description', __('The book you’re looking for isn’t on this shelf. It may have been moved, renamed, or never existed.'))

@section('actions')
<a class="btn primary" href="{{ url('/') }}">Go to Home</a>
<button type="button" class="btn" onclick="history.back()">Go back</button>
@endsection