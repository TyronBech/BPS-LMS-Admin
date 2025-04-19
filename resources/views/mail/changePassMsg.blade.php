<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <title>Change Password</title>
</head>
<body>
<!-- create a email message that the password has been changed -->
  <div class="container mx-auto mt-10">
    <div class="bg-white p-6 rounded-lg shadow-lg">
      <h1 class="text-2xl font-bold mb-4">Password Changed Successfully</h1>
      <p class="text-gray-700 mb-4">Hello {{ $user->first_name }} {{ $user->last_name }},</p>
      <p class="text-gray-700 mb-4">Your password has been changed successfully. If you did not make this change, please contact support immediately.</p>
      <p class="text-gray-700 mb-4">Thank you!</p>
      <p class="text-gray-700">Best regards,</p>
      <p class="text-gray-700">The Team</p>
    </div>
  </div>
</body>
</html>