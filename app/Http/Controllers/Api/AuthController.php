<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\LoginUserRequest;
use App\Models\User;
use App\Traits\ApiResponses;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    use ApiResponses;

    public function login(LoginUserRequest $request)
    {
        // Validate the request
        $request->validated($request->all());

        // Attempt to authenticate the user
        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->error("Invalid credentials", 401);
        }

        // Retrieve the authenticated user
        $user = User::firstWhere('email', $request->email);

        // Return the API token
        return $this->ok(
            "Authenticated",
            [
                'token' => $user->createToken('API token for ' . $user->email)->plainTextToken,
            ]
        );
    }

    public function register()
    {
        //
    }
}
