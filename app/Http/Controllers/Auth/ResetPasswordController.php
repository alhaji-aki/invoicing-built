<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ResetPasswordController extends Controller
{
    /**
     * Reset the given users's password.
     */
    public function __invoke(ResetPasswordRequest $request): JsonResponse
    {
        $response = Password::broker()
            ->reset((array) $request->validated(), function (User $user, string $password) {
                $user->password = $password;
                $user->save();
            });

        if ($response !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => trans($response),
            ]);
        }

        return response()->json([
            'message' => trans($response),
        ]);
    }
}
