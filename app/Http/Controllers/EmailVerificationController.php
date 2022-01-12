<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    public function sendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json('Email already verified', 400);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json('Email verification has been sent', 200);
    }

    public function verify($id)
    {
        $user = User::findOrFail($id);
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
            response()->json('Email has been verified', 200);
            if (Auth::check()) {
                return redirect('http://localhost:3000/dashboard');
            }
            return redirect('http://localhost:3000/login');
        }
        response()->json('Email has been verified', 200);
        return redirect('http://localhost:3000');
    }
}
