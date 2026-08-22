<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
        ]);


        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);


        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
            'token' => $user->createToken('api-token')->plainTextToken,
        ], 201);
    }



    public function login(Request $request)
    {
        $data = $request->validate([
            'email'=>'required|email',
            'password'=>'required',
        ]);


        $user = User::where('email',$data['email'])->first();


        if(!$user || !Hash::check($data['password'],$user->password))
        {
            return response()->json([
                'message'=>'Invalid credentials'
            ],401);
        }


        return response()->json([
            'message'=>'Login successful',
            'token'=>$user->createToken('api-token')->plainTextToken,
        ]);
    }
}