<?php

if (!function_exists('producto_imagen')) {
    /**
     * Obtener la URL de la imagen de un producto
     * Si no tiene imagen, devuelve la imagen por defecto
     *
     * @param mixed $producto Producto o ImagenProducto
     * @param string $size Tamaño (opcional para futuro)
     * @return string URL de la imagen
     */
    function producto_imagen($producto, $size = 'medium')
    {
        // Si es un objeto ImagenProducto directamente
        if ($producto && isset($producto->ruta) && $producto->ruta) {
            return asset('storage/' . $producto->ruta);
        }
        
        // Si es un Producto y tiene imagenPrincipal
        if ($producto && is_object($producto)) {
            if (method_exists($producto, 'imagenPrincipal')) {
                $imagen = $producto->imagenPrincipal;
                if ($imagen && isset($imagen->ruta)) {
                    return asset('storage/' . $imagen->ruta);
                }
            }
            
            // Intenta acceder a la relación como propiedad
            if (isset($producto->imagenPrincipal) && $producto->imagenPrincipal) {
                if (isset($producto->imagenPrincipal->ruta)) {
                    return asset('storage/' . $producto->imagenPrincipal->ruta);
                }
            }
        }
        
        // Imagen por defecto
        return asset('img/NoImagen.jpg');
    }
}

if (!function_exists('imagen_o_defecto')) {
    /**
     * Obtener URL de imagen desde ruta o imagen por defecto
     *
     * @param string|null $ruta Ruta de la imagen en storage
     * @return string URL de la imagen
     */
    function imagen_o_defecto($ruta = null)
    {
        if ($ruta && is_string($ruta) && trim($ruta) !== '') {
            return asset('storage/' . $ruta);
        }
        
        return asset('img/NoImagen.jpg');
    }
}