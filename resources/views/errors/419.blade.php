@extends('errors.layout')

@section('code', '419')
@section('message', __('Page Expired'))
@section('description', __('It looks like this page has been open for too long and your session has expired. Please go back and try again.'))

@section('actions')
<button type="button" class="btn primary" onclick="history.back()">Go back</button>
@endsection