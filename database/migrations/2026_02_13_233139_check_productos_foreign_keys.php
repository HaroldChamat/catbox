<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Verificar y corregir configuraciones de llaves foráneas
     */
    public function up(): void
    {
        // Verificar la configuración actual de la tabla productos
        Schema::table('productos', function (Blueprint $table) {
            // No hacer nada aquí, solo verificar que existe
        });
        
        // Log de la configuración actual
        $tableInfo = DB::select("SHOW CREATE TABLE productos");
        \Log::info('Configuración actual de tabla productos:', (array)$tableInfo);
        
        // Verificar restricciones de llave foránea - CORREGIDO
        $foreignKeys = DB::select("
            SELECT 
                kcu.CONSTRAINT_NAME,
                kcu.COLUMN_NAME,
                kcu.REFERENCED_TABLE_NAME,
                kcu.REFERENCED_COLUMN_NAME,
                rc.DELETE_RULE,
                rc.UPDATE_RULE
            FROM information_schema.KEY_COLUMN_USAGE kcu
            JOIN information_schema.REFERENTIAL_CONSTRAINTS rc 
                ON kcu.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
                AND kcu.TABLE_SCHEMA = rc.CONSTRAINT_SCHEMA
            WHERE kcu.TABLE_NAME = 'productos'
            AND kcu.TABLE_SCHEMA = DATABASE()
            AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        \Log::info('Llaves foráneas de productos:', (array)$foreignKeys);
        
        // Verificar si la llave foránea está configurada como CASCADE
        foreach ($foreignKeys as $fk) {
            if ($fk->COLUMN_NAME === 'categoria_id' && $fk->DELETE_RULE === 'CASCADE') {
                \Log::warning('PROBLEMA ENCONTRADO: La llave foránea categoria_id tiene DELETE CASCADE');
                \Log::warning('Esto podría causar borrados en cascada no deseados');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No hacer nada
    }
};