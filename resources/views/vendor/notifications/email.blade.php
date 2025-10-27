<x-mail::message>

{{-- Greeting --}}
# Hello!

{{-- Intro Lines --}}
You are receiving this email because we received a password reset request for your account at **Bicutan Parochial School Library**.

{{-- Action Button --}}
<x-mail::button :url="$actionUrl" color="primary">
Reset Password
</x-mail::button>

{{-- Outro Lines --}}
This password reset link will expire in 60 minutes.

If you did not request a password reset, no further action is required.

{{-- Salutation --}}
Regards,<br>
**BPS Library System**

{{-- Subcopy --}}
<x-slot:subcopy>
If you're having trouble clicking the **"Reset Password"** button, copy and paste the URL below into your web browser:  
<span class="break-all">[{{ $displayableActionUrl }}]({{ $actionUrl }})</span>
</x-slot:subcopy>
</x-mail::message>