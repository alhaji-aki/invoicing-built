<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\AuthenticatedUserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest:sanctum')->only('store');
        $this->middleware('auth:sanctum')->only('destroy');
    }

    /**
     * Handle a login request to the application.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $request->ensureIsNotRateLimited();

        /** @var \App\Models\User */
        $user = User::where('email', $request->input('email'))->firstOrNew();

        if (! $user->exists || ! Hash::check($request->string('password'), $user->getAuthPassword())) {
            $request->increaseLoginAttempt();

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $request->clearLoginAttempt();

        return response()->json([
            'message' => 'User logged in.',
            'data' => new AuthenticatedUserResource($user),
            'meta' => [
                'token' => explode('|', $user->createToken($user->name)->plainTextToken, 2)[1],
            ],
        ]);
    }

    /**
     * Log the user out of the application.
     */
    public function destroy(Request $request): JsonResponse
    {
        /** @var \App\Models\User */
        $user = $request->user();

        /** @var \Laravel\Sanctum\PersonalAccessToken */
        $token = $user->currentAccessToken();

        $token->delete();

        return response()->json([
            'message' => 'User logged out.',
        ]);
    }
}
