<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pagos MODIFY COLUMN metodo_pago ENUM('tarjeta', 'paypal', 'credito') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE pagos MODIFY COLUMN metodo_pago ENUM('tarjeta', 'paypal') NOT NULL");
    }
};