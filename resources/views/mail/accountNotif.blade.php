<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
    * {
      box-sizing: border-box;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif,
        'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol';
      position: relative;
    }

    body {
      -webkit-text-size-adjust: none;
      background-color: #ffffff;
      color: #718096;
      margin: 0;
      padding: 0;
      width: 100% !important;
    }

    .container,.content {
      margin: 0;
      padding: 0;
      background-color: #f4f4f4;
      color: #333;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-direction: column;
    }
    a {
      color: #2d3748;
      text-decoration: none;
    }

    .inner {
      width: 100%;
      max-width: 600px;
      margin: 0 auto;
      padding: 20px;
      background-color: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
      text-align: left;
    }
    .content :where(img) {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .button {
      text-align: center;
      margin-top: 20px;
    }
    .login {
      margin-top: 20px;
      background-color: #2d3748;
      text-decoration: none;
      color: #fff;
      padding: 10px 20px;
      border-radius: 5px;
      width: 100%;
      height: 100%;
      margin-top: 20px;
    }

    img {
      max-width: 100%;
    }

    .login:hover {
      background-color: #4a5568;
    }
    a {
      color: #2d3748;
      text-decoration: none;
    }

    .logo {
      height: 75px;
      max-height: 75px;
      width: 75px;
      margin: 10px auto;
    }
  </style>
  <title>BPS Library Management System Account</title>
</head>

<body>
  <div class="container">
    <div class="content">
      <img src="{{ asset('img/BPSLogo.png') }}" class="logo" alt="BPS Logo">
      <div class="inner">
        <h2>Account Notification</h2>
        <p>Hello {{ $user->last_name }}, {{ $user->first_name }} {{ $user->middle_name }}</p>
        <p>Your account has been created successfully by the BPS Library Management System.</p>
        <p>Please log in to your account to access the library system.</p>
        <p>Your account details are as follows:</p>
        <ul>
          <li style="text-decoration: none; color: #2d3748"><strong>Email:</strong> {{ $user->email }}</li>
          <li><strong>Password:</strong> {{ $password }}</li>
        </ul>
        <p>Please change your password after logging in for the first time.</p>
        <p>Thank you for using the BPS Library Management System!</p>
        <div class="button">
          <a href="{{ route('login') }}" class="login" style="color: #fff; text-decoration: none">Login</a>
        </div>
      </div>
    </div>
  </div>
</body>

</html>