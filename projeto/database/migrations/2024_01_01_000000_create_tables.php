<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('administradores', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('login')->unique();
            $table->string('senha');  // sem hash
            $table->string('token')->nullable();
            $table->timestamps();
        });

        Schema::create('funcionarios', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('login')->unique();
            $table->string('senha');  // sem hash
            $table->decimal('saldo', 10, 2)->default(0);
            $table->tinyInteger('deleted')->default(0);
            $table->timestamps();
        });

        Schema::create('movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('funcionario_id')->constrained('funcionarios');
            $table->enum('tipo', ['entrada', 'saida']);
            $table->decimal('valor', 10, 2);
            $table->string('descricao')->nullable();
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movimentacoes');
        Schema::dropIfExists('funcionarios');
        Schema::dropIfExists('administradores');
    }
};
