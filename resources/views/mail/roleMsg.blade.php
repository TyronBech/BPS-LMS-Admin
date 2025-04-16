<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <title>You have given a role</title>
</head>
<body>
  <div class="container mx-auto mt-10 flex flex-col items-center text-center">
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
      <h1 class="text-2xl font-bold mb-4">You have been given a role</h1>
      <p class="mb-4">Hello {{ $user->last_name }}, {{ $user->first_name }} {{ $user->middle_name }}</p>
      <p class="mb-4">You have been given the role of <span class="font-bold">{{ $role }}</span>.</p>
      <p class="mb-4">Please log in to your account to see the changes.</p>
      <a href="{{ url('/') }}" class="bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-700">Login</a>
    </div>
  </div>
</body>
</html>