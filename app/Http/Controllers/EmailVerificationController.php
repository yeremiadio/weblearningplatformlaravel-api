<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class EmailVerificationController extends Controller
{
    public function sendVerificationEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return [
                'message' => 'Already Verified'
            ];
        }

        $request->user()->sendEmailVerificationNotification();

        return ['status' => 'verification-link-sent'];
    }

    public function verify($id)
    {
        $user = User::findOrFail($id);
        if (!$user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
            return response()->json('Email has been verified', 200);
        }
        return response()->json('Email has been verified', 200);
    }

    // public function verify(EmailVerificationRequest $request)
    // {
    //     // $request->fulfill();

    //     if (
    //         $request->route('id') == $request->user()->getKey() &&
    //         $request->user()->markEmailAsVerified()
    //     ) {
    //         event(new Verified($request->user()));
    //     }

    //     return redirect($this->redirectPath())->with('verified', true);
    // }
}
