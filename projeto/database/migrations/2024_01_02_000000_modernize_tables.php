<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('funcionarios', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });

        DB::table('funcionarios')
            ->where('deleted', 1)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);

        Schema::table('funcionarios', function (Blueprint $table) {
            $table->dropColumn('deleted');
        });

        foreach (DB::table('administradores')->get() as $admin) {
            if (! str_starts_with($admin->senha, '$2y$')) {
                DB::table('administradores')
                    ->where('id', $admin->id)
                    ->update(['senha' => Hash::make($admin->senha)]);
            }
        }

        foreach (DB::table('funcionarios')->get() as $func) {
            if (! str_starts_with($func->senha, '$2y$')) {
                DB::table('funcionarios')
                    ->where('id', $func->id)
                    ->update(['senha' => Hash::make($func->senha)]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('funcionarios', function (Blueprint $table) {
            $table->tinyInteger('deleted')->default(0);
        });

        DB::table('funcionarios')
            ->whereNotNull('deleted_at')
            ->update(['deleted' => 1]);

        Schema::table('funcionarios', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
};
