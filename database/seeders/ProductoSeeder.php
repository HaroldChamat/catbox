<?php

namespace Database\Seeders;

use App\Models\Producto;
use App\Models\CategoriaProducto;
use App\Models\ImagenProducto;
use Illuminate\Database\Seeder;

class ProductoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener categorías
        $nendoroid = CategoriaProducto::where('slug', 'nendoroid')->first();
        $photocards = CategoriaProducto::where('slug', 'photocards')->first();
        $llaveros = CategoriaProducto::where('slug', 'llaveros')->first();

        // Productos Nendoroid
        $nendoroids = [
            [
                'nombre' => 'Nendoroid Naruto Uzumaki',
                'descripcion' => 'Figura Nendoroid de Naruto Uzumaki con múltiples expresiones y accesorios',
                'precio' => 65.99,
                'stock' => 15,
            ],
            [
                'nombre' => 'Nendoroid Goku',
                'descripcion' => 'Figura Nendoroid de Goku con efectos especiales de ki y poses intercambiables',
                'precio' => 69.99,
                'stock' => 12,
            ],
            [
                'nombre' => 'Nendoroid Hatsune Miku',
                'descripcion' => 'Figura Nendoroid de Hatsune Miku con micrófono y efectos de luz',
                'precio' => 59.99,
                'stock' => 20,
            ],
        ];

        foreach ($nendoroids as $data) {
            $producto = Producto::create(array_merge($data, [
                'categoria_id' => $nendoroid->id,
                'activo' => true,
            ]));

            // Crear imagen de ejemplo
            ImagenProducto::create([
                'producto_id' => $producto->id,
                'ruta' => 'productos/default-nendoroid.jpg',
                'orden' => 1,
                'es_principal' => true,
            ]);
        }

        // Productos Photocards
        $photocardsData = [
            [
                'nombre' => 'Photocard BTS Jungkook',
                'descripcion' => 'Photocard oficial de Jungkook - BTS Map of the Soul',
                'precio' => 12.99,
                'stock' => 30,
            ],
            [
                'nombre' => 'Photocard BLACKPINK Jennie',
                'descripcion' => 'Photocard oficial de Jennie - BLACKPINK Born Pink',
                'precio' => 14.99,
                'stock' => 25,
            ],
            [
                'nombre' => 'Photocard Stray Kids Felix',
                'descripcion' => 'Photocard oficial de Felix - Stray Kids 5-STAR',
                'precio' => 11.99,
                'stock' => 35,
            ],
        ];

        foreach ($photocardsData as $data) {
            $producto = Producto::create(array_merge($data, [
                'categoria_id' => $photocards->id,
                'activo' => true,
            ]));

            ImagenProducto::create([
                'producto_id' => $producto->id,
                'ruta' => 'productos/default-photocard.jpg',
                'orden' => 1,
                'es_principal' => true,
            ]);
        }

        // Productos Llaveros
        $llaverosData = [
            [
                'nombre' => 'Llavero Naruto Shippuden',
                'descripcion' => 'Llavero acrílico de Naruto Shippuden con diseño de doble cara',
                'precio' => 5.99,
                'stock' => 50,
            ],
            [
                'nombre' => 'Llavero Attack on Titan',
                'descripcion' => 'Llavero metálico de la insignia del Cuerpo de Exploración',
                'precio' => 7.99,
                'stock' => 40,
            ],
            [
                'nombre' => 'Llavero Demon Slayer',
                'descripcion' => 'Llavero acrílico de Tanjiro Kamado con espada',
                'precio' => 6.99,
                'stock' => 45,
            ],
            [
                'nombre' => 'Llavero My Hero Academia',
                'descripcion' => 'Llavero acrílico de Deku (Izuku Midoriya)',
                'precio' => 5.99,
                'stock' => 55,
            ],
        ];

        foreach ($llaverosData as $data) {
            $producto = Producto::create(array_merge($data, [
                'categoria_id' => $llaveros->id,
                'activo' => true,
            ]));

            ImagenProducto::create([
                'producto_id' => $producto->id,
                'ruta' => 'productos/default-llavero.jpg',
                'orden' => 1,
                'es_principal' => true,
            ]);
        }
    }
}