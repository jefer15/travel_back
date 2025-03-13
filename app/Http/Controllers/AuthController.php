<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class AuthController extends Controller
{
    // Registro de usuario
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json(['message' => 'Usuario registrado con éxito'], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['error' => 'Credenciales incorrectas'], 401);
        }

        $user = auth()->user();

        $customClaims = [
            'name' => $user->name,
            'email' => $user->email,
            'exp' => Carbon::now()->addDays(7)->timestamp
        ];

        // Generar un nuevo token con los claims personalizados
        $token = JWTAuth::claims($customClaims)->fromUser($user);

        return response()->json([
            'message' => 'Login exitoso',
            'token' => $token
        ]);
    }


    public function userLoggued()
    {
        return response()->json(auth()->user());
    }
}
