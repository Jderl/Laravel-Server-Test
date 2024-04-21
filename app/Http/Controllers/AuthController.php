<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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

            // Revoke existing tokens
            $user->tokens()->delete();

            // Generate new token
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
    /**
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
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'firstName' => 'required',
                'lastName' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:6',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust validation as needed
            ]);

            $user = User::create([
                'name' => $request->input('firstName') . ' ' . $request->input('lastName'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')), // Hash the password
                'image' => $request->input('image'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Account created successfully',
                'user' => $user,
            ], 201);
        } catch (Exception $e) {
            return response()->json(['message' => $e->errors()], 422);
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
    // Update user info
    public function update(Request $request)
    {
        $user = Auth::user();

        try {
            // Validate the request data
            $request->validate([
                'firstName' => 'nullable|string|max:255',
                'lastName' => 'nullable|string|max:255',
                'email' => 'nullable|email|unique:users,email,' . $user->id,
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Assuming image upload
            ]);

            // Update user information
            $user->update([
                'firstName' => $request->input('firstName', $user->firstName),
                'lastName' => $request->input('lastName', $user->lastName),
                'name' => $request->input('firstName', $user->firstName) . ' ' . $request->input('lastName', $user->lastName),
                'email' => $request->input('email', $user->email),
                // Handle image upload if provided
                'image' => $request->hasFile('image') ? $request->file('image')->store('images', 'public') : $user->image,
            ]);

            return response()->json(['message' => 'User updated successfully', 'user' => $user], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    //upload 

    public function uploadProfileImage(Request $request)
    {
        try {
            // Validate the request data
            $validator = Validator::make($request->all(), [
                'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048', // Adjust validation as needed
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = Auth::user();

            // Handle image upload
            $imagePath = $request->file('image')->store('profile_images', 'public');

            // Update user's profile image
            $user->update([
                'image' => $imagePath,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Profile image uploaded successfully',
                'user' => $user,
            ], 200);
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
