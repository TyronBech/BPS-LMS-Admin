<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('admin.auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
        ]);
        if ($validator->fails()) {
            return redirect()->route('login')->with('toast-error', $validator->errors()->first())->withInput();
        }
        $user = User::where('email', $request->input('email'))->first();
        if (!$user) {
            return redirect()->route('login')->with('toast-error', 'Email not found.')->withInput();
        }
        if($user->getRoleNames()->isEmpty()) {
            return redirect()->route('login')->with('toast-error', 'You do not have admin access to this area.')->withInput();
        }
        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status == Password::RESET_LINK_SENT
                    ? back()->with('status', __($status))
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}
