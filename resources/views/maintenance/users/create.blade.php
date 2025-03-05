@extends('layouts.admin-app')
@section('content')
<h1 class="font-semibold text-center text-4xl p-5">Maintenance</h1>
<div class="w-full p-6 bg-white border border-gray-200 rounded-lg shadow dark:bg-gray-800 dark:border-gray-700">
  <div class="flex justify-between">
    <h5 class="mb-1 text-2xl font-bold tracking-tight">Add User</h5>
    <a href="{{ route('maintenance.students') }}" class="inline-flex items-center px-3 py-1 text-sm font-medium text-center text-white bg-blue-700 rounded-lg hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300">
      Back
      <svg class="rtl:rotate-180 w-3.5 h-3.5 ms-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 10">
        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9" />
      </svg>
    </a>
  </div>
  <hr class="h-px my-3 bg-gray-200 border-0">
  <form action="{{ route('maintenance.store-student') }}" class="max-w-2xl mx-auto" method="POST">
    @csrf
    <h6 class="mb-1 text-xl font-semibold tracking-tight">User Information</h6>
    <hr class="h-px my-1 bg-gray-200 border-0">
    <div class="mb-5">
      <label for="rfid" class="block mb-2 text-sm font-medium">RFID Number:</label>
      <input type="text" id="rfid" name="rfid" class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 text-gray-900" placeholder="0123456789" required>
      @error('rfid')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-5">
      <label for="first-name" class="block mb-2 text-sm font-medium">First Name:</label>
      <input type="text" id="first-name" name="first-name" class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 text-gray-900" placeholder="Juan" required>
      @error('first-name')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-5">
      <label for="middle-name" class="block mb-2 text-sm font-medium">Middle Name:</label>
      <input type="text" id="middle-name" name="middle-name" class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 text-gray-900" placeholder="Santos">
      @error('middle-name')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-5">
      <label for="last-name" class="block mb-2 text-sm font-medium">Last Name:</label>
      <input type="text" id="last-name" name="last-name" class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 text-gray-900" placeholder="Dela Cruz" required>
      @error('last-name')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-5">
      <label for="suffix" class="block mb-2 text-sm font-medium">Suffix:</label>
      <input type="text" id="suffix" name="suffix" class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 text-gray-900" placeholder="Jr.">
      @error('suffix')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-5">
      <label class="block mb-2 text-sm font-medium text-gray-900 dark:text-white" for="profile-image">Profile Image:</label>
      <input class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50" id="profile-image" name="profile-image" type="file">
      @error('profile-image')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <h6 class="mb-1 text-md font-bold tracking-tight">Student Credentials <span class="text-sm font-normal">(If the user is a student)</span></h6>
    <hr class="h-px my-1 bg-gray-200 border-0">
    <div class="mb-5">
      <label for="lrn" class="block mb-2 text-sm font-medium">LRN Number:</label>
      <input type="text" id="lrn" name="lrn" class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 text-gray-900" placeholder="0123456789">
      @error('lrn')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-5">
      <label for="grade" class="block mb-2 text-sm font-medium">Grade:</label>
      <input type="text" id="grade" name="grade" class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 text-gray-900" placeholder="12">
      @error('grade')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-5">
      <label for="section" class="block mb-2 text-sm font-medium">Section:</label>
      <input type="text" id="section" name="section" class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 text-gray-900" placeholder="A">
      @error('section')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-5">
      <h6 class="mb-1 text-md font-bold tracking-tight">Employee Credential <span class="text-sm font-normal">(If the user is an employee)</span></h6>
      <hr class="h-px my-1 bg-gray-200 border-0">
      <label for="employee_id" class="block mb-2 text-sm font-medium">Employee ID:</label>
      <input type="text" id="employee_id" name="employee_id" class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 text-gray-900" placeholder="0123456789">
      @error('employee_id')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <h6 class="mb-1 text-md font-bold tracking-tight">User Group</h6>
    <hr class="h-px my-1 bg-gray-200 border-0">
    <div class="mb-5">
      <label for="group" class="block mb-2 text-sm font-medium">Select Group:</label>
      <select id="group" name="group" class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:text-black">
        @foreach($groups as $group)
        <option class="text-sm dark:text-black" value="{{ $group }}">{{ $group }}</option>
        @endforeach
      </select>
      @error('group')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <h6 class="mb-1 text-md font-bold tracking-tight">User Account</h6>
    <hr class="h-px my-1 bg-gray-200 border-0">
    <div class="mb-5">
      <label for="email" class="block mb-2 text-sm font-medium">Email Address:</label>
      <div class="relative">
        <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
          <svg class="w-4 h-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 20 16">
            <path d="m10.036 8.278 9.258-7.79A1.979 1.979 0 0 0 18 0H2A1.987 1.987 0 0 0 .641.541l9.395 7.737Z" />
            <path d="M11.241 9.817c-.36.275-.801.425-1.255.427-.428 0-.845-.138-1.187-.395L0 2.6V14a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V2.5l-8.759 7.317Z" />
          </svg>
        </div>
        <input type="email" id="email" name="email" class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full ps-10 p-2.5 text-gray-900" placeholder="juandelacruz@gmail.com" required>
      </div>
      @error('email')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <div class="mb-5">
      <label for="password" class="block mb-2 text-sm font-medium">Password:</label>
      <input type="password" id="password" name="password" placeholder="••••••••" class="bg-gray-50 border border-gray-300 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 text-gray-900" required />
      @error('password')
      <div class="p-4 my-2 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
        <span class="font-medium">{{ $message }}</span>
      </div>
      @enderror
    </div>
    <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 focus:outline-none dark:focus:ring-blue-800">Submit</button>
  </form>
</div>
@endsection