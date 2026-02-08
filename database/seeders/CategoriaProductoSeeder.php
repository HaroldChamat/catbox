<?php

namespace Database\Seeders;

use App\Models\CategoriaProducto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategoriaProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            [
                'nombre' => 'Nendoroid',
                'descripcion' => 'Figuras Nendoroid coleccionables de alta calidad con partes intercambiables',
                'slug' => Str::slug('Nendoroid'),
            ],
            [
                'nombre' => 'Photocards',
                'descripcion' => 'Tarjetas fotográficas coleccionables de tus artistas favoritos',
                'slug' => Str::slug('Photocards'),
            ],
            [
                'nombre' => 'Llaveros',
                'descripcion' => 'Llaveros temáticos de anime, manga y K-pop',
                'slug' => Str::slug('Llaveros'),
            ],
        ];

        foreach ($categorias as $categoria) {
            CategoriaProducto::create($categoria);
        }
    }
}