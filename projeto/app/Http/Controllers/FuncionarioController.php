<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FuncionarioController extends Controller
{
    public function index()
    {
        $funcionarios = DB::select("SELECT id, nome, login, saldo FROM funcionarios WHERE deleted = 0");

        return response()->json($funcionarios);
    }

    public function show($id)
    {
        $funcionario = DB::select("SELECT id, nome, login, saldo FROM funcionarios WHERE id = $id AND deleted = 0");

        if (empty($funcionario)) {
            return response()->json(['erro' => 'Funcionário não encontrado'], 404);
        }

        return response()->json($funcionario[0]);
    }

    public function store(Request $request)
    {
        $nome  = $request->nome;
        $login = $request->login;
        $senha = $request->senha;

        $existe = DB::select("SELECT id FROM funcionarios WHERE login = '$login' AND deleted = 0");
        if (!empty($existe)) {
            return response()->json(['erro' => 'Login já cadastrado'], 422);
        }

        DB::insert("INSERT INTO funcionarios (nome, login, senha, saldo, deleted) VALUES ('$nome', '$login', '$senha', 0, 0)");

        $id = DB::getPdo()->lastInsertId();

        return response()->json(['mensagem' => 'Funcionário criado', 'id' => $id], 201);
    }

    public function update(Request $request, $id)
    {
        $nome  = $request->nome;
        $login = $request->login;

        $existe = DB::select("SELECT id FROM funcionarios WHERE login = '$login' AND id != $id AND deleted = 0");
        if (!empty($existe)) {
            return response()->json(['erro' => 'Login já cadastrado'], 422);
        }

        DB::update("UPDATE funcionarios SET nome = '$nome', login = '$login' WHERE id = $id");

        return response()->json(['mensagem' => 'Funcionário atualizado']);
    }

    public function destroy($id)
    {
        DB::update("UPDATE funcionarios SET deleted = 1 WHERE id = $id");

        return response()->json(['mensagem' => 'Funcionário removido']);
    }
}
