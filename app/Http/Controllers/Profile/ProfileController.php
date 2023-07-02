<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Http\Resources\AuthenticatedUserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth:sanctum']);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request): JsonResponse
    {
        return response()->json([
            'message' => 'Get user.',
            'data' => new AuthenticatedUserResource($request->user()),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $data = array_filter((array) $request->validated());

        abort_if(empty($data), 400, 'No data submitted.');

        /** @var \App\Models\User */
        $user = $request->user();

        $emailHasChanged = isset($data['email']) && $data['email'] !== $user->email;

        $user->update(array_merge($data, [
            'email_verified_at' => $emailHasChanged ? null : $user->email_verified_at,
        ]));

        if ($emailHasChanged) {
            $user->sendEmailVerificationNotification();
        }

        return response()->json([
            'message' => 'Profile updated successful.',
        ]);
    }
}
