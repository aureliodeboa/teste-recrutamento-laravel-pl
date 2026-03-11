<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $login = $request->login;
        $senha = $request->senha;

        $admin = DB::select("SELECT * FROM administradores WHERE login = '$login' AND senha = '$senha'");

        if (empty($admin)) {
            return response()->json(['erro' => 'Credenciais inválidas'], 401);
        }

        $admin = $admin[0];

        $token = rand(100000, 999999);

        DB::update("UPDATE administradores SET token = '$token' WHERE id = $admin->id");

        return response()->json([
            'mensagem' => 'Login realizado com sucesso',
            'token'    => $token,
            'admin'    => [
                'id'    => $admin->id,
                'nome'  => $admin->nome,
                'login' => $admin->login,
                'senha' => $admin->senha,
            ],
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->header('Authorization');

        DB::update("UPDATE administradores SET token = NULL WHERE token = '$token'");

        return response()->json(['mensagem' => 'Logout realizado']);
    }
}
