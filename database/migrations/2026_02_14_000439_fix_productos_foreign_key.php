<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Primero, verificar el nombre exacto de la foreign key
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'productos' 
            AND COLUMN_NAME = 'categoria_id'
            AND TABLE_SCHEMA = DATABASE()
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        if (empty($foreignKeys)) {
            \Log::warning('No se encontró foreign key para categoria_id');
            return;
        }
        
        $constraintName = $foreignKeys[0]->CONSTRAINT_NAME;
        \Log::info('Foreign key encontrada: ' . $constraintName);
        
        // Eliminar la foreign key actual
        DB::statement("ALTER TABLE productos DROP FOREIGN KEY {$constraintName}");
        \Log::info('Foreign key eliminada exitosamente');
        
        // Agregar la nueva foreign key SIN CASCADE
        DB::statement("
            ALTER TABLE productos 
            ADD CONSTRAINT {$constraintName} 
            FOREIGN KEY (categoria_id) 
            REFERENCES categorias_producto(id) 
            ON DELETE RESTRICT 
            ON UPDATE CASCADE
        ");
        \Log::info('Nueva foreign key creada con RESTRICT');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Obtener el nombre de la foreign key
        $foreignKeys = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.KEY_COLUMN_USAGE 
            WHERE TABLE_NAME = 'productos' 
            AND COLUMN_NAME = 'categoria_id'
            AND TABLE_SCHEMA = DATABASE()
            AND REFERENCED_TABLE_NAME IS NOT NULL
        ");
        
        if (empty($foreignKeys)) {
            return;
        }
        
        $constraintName = $foreignKeys[0]->CONSTRAINT_NAME;
        
        // Volver a CASCADE
        DB::statement("ALTER TABLE productos DROP FOREIGN KEY {$constraintName}");
        
        DB::statement("
            ALTER TABLE productos 
            ADD CONSTRAINT {$constraintName} 
            FOREIGN KEY (categoria_id) 
            REFERENCES categorias_producto(id) 
            ON DELETE CASCADE 
            ON UPDATE CASCADE
        ");
    }
};