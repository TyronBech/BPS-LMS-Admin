@extends('errors.layout')

@section('code', '503')
@section('message', __('Service Unavailable'))
@section('description', __('Our stacks are being reorganized and dusted. Service is temporarily unavailable. Please check back shortly.'))

@section('actions')
<a class="btn primary" href="javascript:location.reload()">Try again</a>
<a class="btn" href="{{ url('/') }}">Go to Home</a>
@endsection