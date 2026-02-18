<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void {
        DB::statement("ALTER TABLE ordenes MODIFY COLUMN estado ENUM('pendiente', 'procesando', 'enviado', 'entregado', 'cancelado', 'devuelto') NOT NULL DEFAULT 'pendiente'");
    }

    public function down(): void {
        DB::statement("ALTER TABLE ordenes MODIFY COLUMN estado ENUM('pendiente', 'procesando', 'enviado', 'entregado', 'cancelado') NOT NULL DEFAULT 'pendiente'");
    }
};