@extends('errors.layout')

@section('code', '429')
@section('message', __('Too Many Requests'))
@section('description', __('You\'re trying to access pages too quickly, like flipping through books too fast. Please slow down and wait a moment before trying again.'))

@section('actions')
<button type="button" class="btn primary" onclick="history.back()">Go back</button>
@endsection