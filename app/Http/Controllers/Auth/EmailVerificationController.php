<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
        $this->middleware('signed:relative')->only('verify');
        $this->middleware('throttle:6,1')->only('verify', 'resend');
    }

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function verify(Request $request): JsonResponse
    {
        /** @var \App\Models\User */
        $user = $request->user();

        abort_if($user->hasVerifiedEmail(), 403, 'Your email address is already verified.');

        // @phpstan-ignore-next-line
        if (! hash_equals((string) $request->route('id'), (string) $user->uuid)) {
            throw new AuthorizationException;
        }

        // @phpstan-ignore-next-line
        if (! hash_equals($request->route('hash'), sha1($user->getEmailForVerification()))) {
            throw new AuthorizationException;
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json([
            'message' => 'Email verified successfully',
        ]);
    }

    /**
     * Resend the email verification notification.
     */
    public function resend(Request $request): JsonResponse
    {
        /** @var \App\Models\User */
        $user = $request->user();

        abort_if($user->hasVerifiedEmail(), 403, 'Your email address is already verified.');

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Email verification sent successfully',
        ]);
    }
}
