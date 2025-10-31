@extends('errors.layout')

@section('code', '402')
@section('message', __('Payment Required'))
@section('description', __('It appears this section of the library is for members only. Please check your subscription to gain access.'))

@section('actions')
<a class="btn primary" href="{{ url('/billing') }}">Check Subscription</a>
<button type="button" class="btn" onclick="history.back()">Go back</button>
@endsection