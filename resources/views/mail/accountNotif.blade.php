<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <title>BPS Library Management System Account</title>
</head>
<body>
  <div class="container mx-auto mt-10 flex flex-col items-center text-center">
    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
      <h1 class="text-2xl font-bold mb-4">Account Notification</h1>
      <p class="mb-4">Hello {{ $user->last_name }}, {{ $user->first_name }} {{ $user->middle_name }}</p>
      <p class="mb-4">Your account has been created successfully by the BPS Library Management System.</p>
      <p class="mb-4">Please log in to your account to access the library system.</p>
      <p class="mb-4">Your account details are as follows:</p>
      <ul class="list-disc list-inside mb-4">
        <li><strong>Email:</strong> {{ $user->email }}</li>
        <li><strong>Password:</strong> {{ $password }}</li>
      </ul>
      <p class="mb-4">Please change your password after logging in for the first time.</p>
      <p class="mb-4">Thank you for using the BPS Library Management System!</p>
      <a href="{{ route('login') }}" class="bg-blue-500 text-white font-bold py-2 px-4 rounded hover:bg-blue-700">Login</a>
    </div>
  </div>
</body>
</html>