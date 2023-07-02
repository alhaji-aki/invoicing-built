<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    /**
     * Send a reset link to the given user.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = (array) $request->validate([
            'email' => ['required', 'string', 'email'],
        ]);

        $response = DB::transaction(fn () => Password::broker()->sendResetLink($validated));

        if ($response !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => trans($response),
            ]);
        }

        return response()->json([
            'message' => trans($response),
        ]);
    }
}
