<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Cache; // Add this import
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AdminLoginRequest extends FormRequest
{
    /**
    * Determine if the user is authorized to make this request.
    */
   public function authorize(): bool
   {
       return true;
   }

   /**
    * Get the validation rules that apply to the request.
    *
    * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
    */
   public function rules(): array
   {
       return [
        'email' => [
            'required',
            'email',
            'max:255',
            function ($attribute, $value, $fail) {
                if (preg_match('/(select|union|insert|update|delete|drop|--|;)/i', strtolower($value))) {
                    $fail('The ' . $attribute . ' is invalid.');
                    Log::warning('Possible SQL injection attempt for email', [
                        'field' => $attribute,
                        'input' => $value,
                        'ip' => request()->ip(),
                    ]);
                }
            }
        ],
        'password' => [
            'required',
            'string',
            function ($attribute, $value, $fail) {
                if (preg_match('/(select|union|insert|update|delete|drop|--|;)/i', strtolower($value))) {
                    $fail('The ' . $attribute . ' is invalid.');
                    Log::warning('Possible SQL injection attempt for password', [
                        'field' => $attribute,
                        'input' => $value,
                        'ip' => request()->ip(),
                    ]);
                }
            }
        ]
    ];
   }

   /**
    * Attempt to authenticate the request's credentials.
    *
    * @throws \Illuminate\Validation\ValidationException
    */
   public function authenticate(): void
   {
       $this->ensureIsNotRateLimited();

       if (! Auth::guard('admin')->attempt($this->only('email', 'password'), $this->boolean('remember'))) {
           
           // Calculate dynamic decay based on previous lockouts
           $penaltyKey = $this->throttleKey() . '.penalty';
           $lockoutLevel = Cache::get($penaltyKey, 0);
           
           // Decay duration tiers: 1 min, 5 mins, 15 mins, 1 hour
           $decaySeconds = match($lockoutLevel) {
               0 => 60,
               1 => 300,
               2 => 900,
               default => 3600,
           };

           RateLimiter::hit($this->throttleKey(), $decaySeconds);

           // If this failure caused a lockout, increment the penalty level for next time
           if (RateLimiter::tooManyAttempts($this->throttleKey(), 3)) {
               Cache::put($penaltyKey, $lockoutLevel + 1, now()->addDay());
           }

           throw ValidationException::withMessages([
               'email' => trans('auth.failed'),
           ]);
       }

       RateLimiter::clear($this->throttleKey());
   }

   /**
    * Ensure the login request is not rate limited.
    *
    * @throws \Illuminate\Validation\ValidationException
    */
   public function ensureIsNotRateLimited(): void
   {
       if (! RateLimiter::tooManyAttempts($this->throttleKey(), 3)) {
           return;
       }

       event(new Lockout($this));

       $seconds = RateLimiter::availableIn($this->throttleKey());

       session()->flash('lockout_time', $seconds);

       throw ValidationException::withMessages([
           'email' => trans('auth.throttle', [
               'seconds' => $seconds,
               'minutes' => ceil($seconds / 60),
           ]),
       ]);
   }

   /**
    * Get the rate limiting throttle key for the request.
    */
   public function throttleKey(): string
   {
       return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
   }
}
