<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['guest:sanctum']);
    }

    /**
     * Handle a registration request for the application.
     */
    public function __invoke(RegisterRequest $request): JsonResponse
    {
        DB::transaction(function () use ($request) {
            $user = User::create((array) $request->validated());

            event(new Registered($user));
        });

        return response()->json([
            'message' => 'User registered successful.',
        ]);
    }
}
