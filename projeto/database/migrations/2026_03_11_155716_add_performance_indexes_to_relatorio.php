<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Índice composto cobrindo o JOIN + agregações CASE WHEN (tipo, valor)
        // O banco usa este índice para resolver toda a agregação sem tocar nas linhas reais (covering index)
        Schema::table('movimentacoes', function (Blueprint $table) {
            $table->index(['funcionario_id', 'tipo', 'valor'], 'idx_mov_funcionario_tipo_valor');
        });

        // Índice em deleted_at para o filtro WHERE NULL na paginação e no COUNT
        Schema::table('funcionarios', function (Blueprint $table) {
            $table->index('deleted_at', 'idx_func_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('movimentacoes', function (Blueprint $table) {
            $table->dropIndex('idx_mov_funcionario_tipo_valor');
        });

        Schema::table('funcionarios', function (Blueprint $table) {
            $table->dropIndex('idx_func_deleted_at');
        });
    }
};
