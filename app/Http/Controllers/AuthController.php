<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    /**
     * Show the form for creating a new resource.
     */
    public function register(Request $request)
    {

        $fields = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users,email',
            'password' => 'required|string|confirmed',
            'password_confirmation' => 'required|string',
        ]);


        $user = User::create([
            'name'=> $fields['name'],
            'email'=> $fields['email'],
            'password'=> bcrypt($fields['password']),
        ]);

        $token = $user->createToken('dripstoreapp')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token,
            'message' => 'User registered successfully'
        ];

        return response($response, 201);
    }

    public function login(Request $request)
    {
        //
        $fields = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $fields['email'])->first();

        if(!$user || !Hash::check($fields['password'], $user->password)){
            return response([
                'message' => 'Bad Credentials'
            ], 401);
        }

        $token = $user->createToken('dripstoreapp')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        $user = Auth::user();

        return response([
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'profile_picture' => $user->profile_picture ? url('images/' . $user->profile_picture) : null,
            ]
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function updateUserProfile(Request $request)
    {
        // 1. Cari user yang login terlebih dahulu.
        $user = Auth::user();

        $validate = $request->validate([
            'name' => 'string',
            'phone' => 'string',
            'address' => 'string',
            'profile_picture' => 'file|extensions:jpg,png,jpeg|mimes:jpg,png,jpeg|max:2048',
        ]);


        // 2. Upload gambar jika ada
        if($request->hasFile('profile_picture')){
            $file = $request['profile_picture'];
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('images'), $filename);
            $validate['profile_picture'] = $filename;
        }

        // 3. Update data user
        $user->name = $validate['name'] ?? $user->name;
        $user->phone = $validate['phone'] ?? $user->phone;
        $user->address = $validate['address'] ?? $user->address;
        $user->profile_picture = $validate['profile_picture'] ?? $user->profile_picture;
        $user->save();

        return response([
            'user' => [
                'name' => $user->name,
                'phone' => $user->phone,
                'address' => $user->address,
                'profile_picture' => $user->profile_picture ? url('images/' . $user->profile_picture) : null,
            ]
        ], 201);
    }

    public function updatePassword(Request $request){
        $user = Auth::user();

        $validate = $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|confirmed',
            'new_password_confirmation' => 'required|string',
        ], [
            'old_password.required' => 'Old Password is required',
            'new_password.required' => 'New Password is required',
            'new_password_confirmation.required' => 'New Password Confirmation is required',
            'new_password.confirmed' => 'New Password Confirmation does not match',
        ]);

        if(!Hash::check($validate['old_password'], $user->password)){
            return response([
                'message' => 'Old Password is incorrect'
            ], 401);
        }

        $user->password = bcrypt($validate['new_password']);
        $user->save();

        return response([
            'message' => 'Password updated successfully'
        ], 201);
    }
}
