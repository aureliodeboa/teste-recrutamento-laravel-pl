<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Resources\AdministradorResource;
use App\Models\Administrador;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $admin = Administrador::where('login', $request->login)->first();

        if (! $admin || ! Hash::check($request->senha, $admin->senha)) {
            return response()->json(['erro' => 'Credenciais inválidas'], 401);
        }

        $token = $admin->createToken('api-token')->plainTextToken;

        return response()->json([
            'mensagem' => 'Login realizado com sucesso',
            'token'    => $token,
            'admin'    => new AdministradorResource($admin),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['mensagem' => 'Logout realizado']);
    }
}
