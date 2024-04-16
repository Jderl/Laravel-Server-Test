<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Login
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required',
            ]);

            $credentials = $request->only('email', 'password');

            if (!Auth::attempt($credentials)) {
                return response()->json(['message' => 'Invalid email or password'], 401);
            }

            $user = Auth::user();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login successfully',
                'user' => $user,
                'token' => $token,
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // Register
    public function register(Request $request)
    {
        try {
            $request->validate([
                'firstName' => 'required',
                'lastName' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'image' => 'nullable', // Adjust validation as needed
                'accountType' => 'required',
                'provider' => 'nullable',
            ]);

            $user = User::create([
                'name' => $request->input('firstName') . ' ' . $request->input('lastName'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')), // Hash the password
                'image' => $request->input('image'),
                'accountType' => $request->input('accountType'),
                'provider' => $request->input('provider'),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            // Send email verification if account type is writer
            if ($request->input('accountType') === 'Writer') {
                // You can add email sending logic here
            }

            return response()->json([
                'success' => true,
                'message' => 'Account created successfully',
                'user' => $user,
                'token' => $token,
            ], 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    // Get all users
    public function getAllUsers()
    {
        try {
            // Fetch all users from the database
            $users = User::all();

            return response()->json([
                'success' => true,
                'users' => $users,
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
