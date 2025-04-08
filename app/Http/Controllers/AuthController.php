<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use App\Models\Employee;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use App\Models\User;


class AuthController extends Controller
{


    public function Token_Register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users|max:255', // Change email to username
            'password' => 'required|string|min:3',
            'position' => 'required|string|max:255', // Validate position
            'active' => 'required|boolean', // Validate active as a boolean
        ]);

        try {
            $user = User::create([
                'name' => $validatedData['name'],
                'username' => $validatedData['username'], // Save username
                'password' => Hash::make($validatedData['password']),
                'position' => $validatedData['position'], // Save position
                'active' => $validatedData['active'], // Save active status
            ]);

            return response()->json([
                'status' => true,
                'message' => 'User Registered Successfully',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Registration Failed',
            ], 500);
        }
    }

    public function Token_Login(Request $request)
    {
        // Attempt to authenticate the user with the provided username and password
        // First check if the username exists
        // First check if username and password are provided
        if (empty($request->username) || empty($request->password)) {
            return response([
            'status' => false,
            'message' => 'Invalid Credentials',
            'errors' => [
                'username' => empty($request->username) ? ['Please enter username'] : [],
                'password' => empty($request->password) ? ['Please enter password'] : []
            ]
            ], 401);
        }

        $user = User::where('username', $request->username)->first();
        if (!$user) {
            return response([
            'status' => false,
            'message' => 'Invalid Credentials',
            'errors' => [
            'username' => ['Username does not exist'],
            'password' => ['Please enter password']
            ]
            ], 401);
        }

        // Then check if the password is correct
        if (!Hash::check($request->password, $user->password)) {
            return response([
            'status' => false,
            'message' => 'Invalid Credentials',
            'errors' => [
                'password' => ['Wrong password']
            ]
            ], 401);
        }

        // If both checks pass, authenticate the user
        Auth::login($user);

        $user = Auth::user();

        // Check if the user is active
        if (!$user->active) {
            return response([
                'status' => false,
                'message' => 'Your account is inactive. Please contact the administrator.',
            ], 403);
        }

        // Generate a token for the user
        $token = $user->createToken('my-secret-token')->plainTextToken;

        // Set the token in a secure cookie
        $cookie = cookie('auth_token', $token, 60 * 24, null, null, true, true, false, 'None');

        return response([
            'status' => true,
            'message' => 'Login Successfully',
            'user' => [
                'name' => $user->name,
                'position' => $user->position,
            ],
            'token' => $token,
        ])->withCookie($cookie);
    }

    public function Token_Logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $user->tokens()->delete();
        }

        $cookie = cookie()->forget('auth_token');

        return response([
            'status' => true,
            'message' => 'Logout Successfully',
        ])->withCookie($cookie);
    }
}
?>
