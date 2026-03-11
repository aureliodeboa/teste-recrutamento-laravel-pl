<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('administradores')->updateOrInsert(
            ['login' => 'admin'],
            ['nome' => 'Administrador', 'senha' => '123456']  // senha em texto puro, sem hash
        );

        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('movimentacoes')->truncate();
        DB::table('funcionarios')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $funcionariosBase = [
            ['nome' => 'João Silva', 'login' => 'joao.silva'],
            ['nome' => 'Maria Souza', 'login' => 'maria.souza'],
            ['nome' => 'Carlos Pereira', 'login' => 'carlos.pereira'],
        ];

        $funcionarios = [];
        for ($i = 0; $i < 5000; $i++) {
            $base = $funcionariosBase[$i % 3];
            $funcionarios[] = [
                'nome'    => $base['nome'] . ' ' . ($i + 1),
                'login'   => $base['login'] . '.' . $i,
                'senha'   => '123456',
                'saldo'   => rand(0, 2000) / 10,
                'deleted' => 0,
            ];
        }

        $descricoes = ['Bonificação por entrega', 'Troca por recarga', 'Bônus mensal', 'Resgate produto', 'Crédito extra'];
        $movimentacoesBuffer = [];
        $bufferSize = 1000;

        foreach (array_chunk($funcionarios, 100) as $chunk) {
            DB::table('funcionarios')->insert($chunk);
            $maxId = DB::table('funcionarios')->max('id');
            $funcionarioIds = DB::table('funcionarios')
                ->where('id', '>', $maxId - count($chunk))
                ->orderBy('id')
                ->pluck('id');

            foreach ($funcionarioIds as $funcionarioId) {
                $qtdMov = rand(10, 500);
                for ($j = 0; $j < $qtdMov; $j++) {
                    $tipo = $j % 2 === 0 ? 'entrada' : 'saida';
                    $valor = rand(10, 500) / 10;
                    $descricao = $descricoes[array_rand($descricoes)];
                    $movimentacoesBuffer[] = [
                        'funcionario_id' => $funcionarioId,
                        'tipo'           => $tipo,
                        'valor'          => $valor,
                        'descricao'      => $descricao,
                        'created_at'      => now()->subDays(rand(0, 90)),
                    ];

                    if (count($movimentacoesBuffer) >= $bufferSize) {
                        DB::table('movimentacoes')->insert($movimentacoesBuffer);
                        $movimentacoesBuffer = [];
                    }
                }
            }
        }

        if (!empty($movimentacoesBuffer)) {
            DB::table('movimentacoes')->insert($movimentacoesBuffer);
        }
    }
}
