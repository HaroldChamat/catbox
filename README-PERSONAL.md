# 📚 Documentación Personal - Estructura del Proyecto Catbox

> **Guía detallada de archivos y carpetas del proyecto e-commerce**

---

## 📋 Tabla de Contenidos

- [Visión General](#visión-general)
- [Estructura de Carpetas](#estructura-de-carpetas)
- [Directorio App](#directorio-app)
- [Directorio Database](#directorio-database)
- [Directorio Public](#directorio-public)
- [Directorio Resources](#directorio-resources)
- [Directorio Routes](#directorio-routes)
- [Directorio Storage](#directorio-storage)
- [Archivos de Configuración](#archivos-de-configuración)
- [Notas Personales](#notas-personales)

---

## 🎯 Visión General

Este documento es una guía personal que explica **cada archivo y carpeta** del proyecto Catbox. Es útil para:
- Entender qué hace cada archivo
- Saber dónde agregar nuevas funcionalidades
- Mantener el código organizado
- Facilitar el onboarding de nuevos desarrolladores

---

## 📁 Estructura de Carpetas

```
catbox/
├── app/                    # Lógica de la aplicación (Modelos, Controladores, Middleware)
├── bootstrap/              # Archivos de inicio de Laravel (no tocar normalmente)
├── config/                 # Archivos de configuración
├── database/              # Migraciones, Seeders y Factories
├── public/                # Punto de entrada web (archivos públicos)
├── resources/             # Vistas, assets sin compilar
├── routes/                # Definición de rutas
├── storage/               # Archivos generados (logs, caché, uploads)
├── tests/                 # Tests unitarios y de integración
├── vendor/                # Dependencias de Composer (no versionar)
├── .env                   # Variables de entorno (no versionar)
├── .env.example           # Plantilla de variables de entorno
├── composer.json          # Dependencias PHP
├── package.json           # Dependencias JavaScript
└── artisan                # CLI de Laravel
```

---

## 📂 Directorio App

**Ubicación:** `/app`

Contiene toda la lógica de negocio de la aplicación.

### 📁 app/Http/Controllers

**Propósito:** Controladores que manejan las peticiones HTTP y devuelven respuestas.

#### **AdminController.php**
```php
// Ubicación: app/Http/Controllers/AdminController.php
```
**¿Qué hace?**
- Maneja el dashboard administrativo principal
- Muestra métricas generales (ventas totales, órdenes, usuarios, productos)
- Calcula top 5 productos más vendidos
- Muestra órdenes pendientes y productos con stock bajo
- Genera estadísticas del mes actual

**Métodos principales:**
- `index()` - Dashboard principal con todas las métricas
- Usa consultas Eloquent para calcular totales y estadísticas

**Cuándo modificar:**
- Para agregar nuevas métricas al dashboard
- Para cambiar KPIs mostrados
- Para ajustar alertas de stock bajo

---

#### **CarritoController.php**
```php
// Ubicación: app/Http/Controllers/CarritoController.php
```
**¿Qué hace?**
- Gestiona todo el carrito de compras
- Agrega/elimina productos del carrito
- Actualiza cantidades de items
- Calcula subtotales y totales
- Valida stock disponible antes de agregar items

**Métodos principales:**
- `index()` - Muestra el carrito con todos sus items
- `agregar(Request $request)` - Agrega producto al carrito
- `actualizar(Request $request, $itemId)` - Actualiza cantidad de un item
- `eliminar($itemId)` - Elimina item del carrito
- `obtenerTotal()` - Calcula total del carrito (API)

**Cuándo modificar:**
- Para agregar descuentos al carrito
- Para implementar cupones
- Para cambiar lógica de validación de stock
- Para agregar límites de cantidad por producto

**Lógica importante:**
```php
// Validación de stock antes de agregar
if ($producto->stock < $cantidad) {
    return redirect()->back()->with('error', 'Stock insuficiente');
}
```

---

#### **CategoriaProductoController.php**
```php
// Ubicación: app/Http/Controllers/CategoriaProductoController.php
```
**¿Qué hace?**
- CRUD completo de categorías de productos
- Genera slugs únicos automáticamente
- Valida unicidad de nombres
- Permite activar/desactivar categorías

**Métodos principales:**
- `index()` - Lista todas las categorías
- `create()` - Formulario de nueva categoría
- `store(Request $request)` - Crea categoría
- `edit($id)` - Formulario de edición
- `update(Request $request, $id)` - Actualiza categoría
- `destroy($id)` - Elimina categoría (solo si no tiene productos)

**Validaciones:**
```php
$request->validate([
    'nombre' => 'required|string|max:100|unique:categorias_producto',
    'descripcion' => 'nullable|string',
]);
```

**Cuándo modificar:**
- Para agregar campos a las categorías (ej: icono, color)
- Para cambiar reglas de validación
- Para implementar subcategorías

---

#### **EstadisticaController.php**
```php
// Ubicación: app/Http/Controllers/EstadisticaController.php
```
**¿Qué hace?**
- **Controlador más complejo del sistema**
- Genera estadísticas avanzadas de ventas, productos y clientes
- Crea gráficos interactivos con Chart.js
- Permite filtrado por fechas

**Métodos principales:**

1. **`dashboard()`** - Panel general de estadísticas
   - Ventas por mes (últimos 6 meses)
   - Productos más vendidos
   - Categorías más populares
   - Comparación con mes anterior

2. **`ventas()`** - Análisis detallado de ventas
   - Ventas por día del mes
   - Ventas por día de la semana
   - Ventas por hora del día
   - Métodos de pago más usados
   - Tendencias y patrones

3. **`productos()`** - Análisis de productos
   - Top 10 productos más vendidos
   - Productos sin ventas
   - Stock bajo (< 10 unidades)
   - Rendimiento por categoría
   - Valor del inventario

4. **`clientes()`** - Análisis de clientes
   - Top 10 clientes
   - Clientes nuevos vs. recurrentes
   - Ticket promedio por cliente
   - Segmentación por cantidad de compras

**Datos que prepara para gráficos:**
```php
// Ejemplo: Datos para Chart.js
$ventasPorMes = [
    'labels' => ['Enero', 'Febrero', 'Marzo', ...],
    'data' => [15000, 18000, 22000, ...]
];
```

**Cuándo modificar:**
- Para agregar nuevos tipos de reportes
- Para cambiar períodos de análisis
- Para agregar exportación a PDF/Excel
- Para implementar comparaciones entre períodos

---

#### **OrdenController.php**
```php
// Ubicación: app/Http/Controllers/OrdenController.php
```
**¿Qué hace?**
- Gestiona el proceso completo de checkout
- Crea órdenes desde el carrito
- Actualiza estados de órdenes
- Gestiona pagos y direcciones de entrega

**Métodos principales:**

1. **`checkout()`** - Inicia el proceso de compra
   - Valida que el carrito no esté vacío
   - Muestra formulario de checkout

2. **`procesarCheckout(Request $request)`** - Procesa la orden
   - Valida datos del formulario
   - Crea la orden en estado "pendiente"
   - Crea detalles de orden (productos)
   - Reduce stock de productos
   - Crea registro de pago
   - Asigna dirección de entrega
   - Vacía el carrito
   - Genera estadísticas

3. **`show($id)`** - Muestra detalle de una orden

4. **`actualizar(Request $request, $id)`** - Actualiza estado de orden (admin)
   - Estados: pendiente, procesando, enviado, entregado, cancelado

**Validaciones importantes:**
```php
// Validación de checkout
$request->validate([
    'metodo_pago' => 'required|in:tarjeta,paypal',
    'direccion_entrega_id' => 'required|exists:direcciones_entrega,id',
    'notas_orden' => 'nullable|string',
]);
```

**Lógica crítica:**
```php
// Reducción de stock al crear orden
foreach ($items as $item) {
    $producto = Producto::find($item->producto_id);
    $producto->stock -= $item->cantidad;
    $producto->save();
}
```

**Cuándo modificar:**
- Para agregar nuevos métodos de pago
- Para implementar notificaciones por email
- Para agregar validación de dirección
- Para implementar cancelación de órdenes con devolución de stock

---

#### **ProductoController.php**
```php
// Ubicación: app/Http/Controllers/ProductoController.php
```
**¿Qué hace?**
- CRUD completo de productos (parte pública y admin)
- Gestión de múltiples imágenes por producto
- Sistema de búsqueda y filtrado
- Validación de stock

**Métodos principales (Públicos):**

1. **`index()`** - Lista todos los productos disponibles
   - Solo muestra productos activos
   - Paginación de 12 items
   - Incluye imágenes y categorías

2. **`show($id)`** - Detalle de producto individual
   - Muestra todas las imágenes
   - Información completa
   - Productos relacionados de la misma categoría

3. **`buscar(Request $request)`** - Búsqueda de productos
   - Busca por nombre o descripción
   - Filtrado por categoría
   - Filtrado por rango de precio

**Métodos principales (Admin):**

1. **`admin_index()`** - Lista productos para admin
   - Muestra todos los productos (activos e inactivos)
   - Filtros de búsqueda
   - Indicadores de stock

2. **`create()`** - Formulario de nuevo producto

3. **`store(Request $request)`** - Crea nuevo producto
   - Valida datos
   - Genera slug único
   - Sube múltiples imágenes
   - Marca imagen principal

4. **`edit($id)`** - Formulario de edición

5. **`update(Request $request, $id)`** - Actualiza producto
   - Actualiza datos básicos
   - Gestiona imágenes (nuevas y eliminar)
   - Cambia imagen principal

6. **`destroy($id)`** - Elimina producto
   - Solo si no está en órdenes
   - Elimina imágenes del storage

7. **`toggleEstado($id)`** - Activa/desactiva producto

**Validaciones:**
```php
$request->validate([
    'nombre' => 'required|string|max:255',
    'descripcion' => 'required|string',
    'precio' => 'required|numeric|min:0',
    'stock' => 'required|integer|min:0',
    'categoria_id' => 'required|exists:categorias_producto,id',
    'imagenes.*' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
]);
```

**Lógica de imágenes:**
```php
// Subir múltiples imágenes
if ($request->hasFile('imagenes')) {
    foreach ($request->file('imagenes') as $index => $imagen) {
        $nombre = time() . '_' . $index . '.' . $imagen->getClientOriginalExtension();
        $ruta = $imagen->storeAs('public/productos', $nombre);
        
        ImagenProducto::create([
            'producto_id' => $producto->id,
            'ruta_imagen' => 'productos/' . $nombre,
            'es_principal' => $index === 0, // Primera imagen es principal
        ]);
    }
}
```

**Cuándo modificar:**
- Para agregar nuevos campos (ej: dimensiones, peso)
- Para implementar variantes de productos
- Para mejorar búsqueda (full-text search)
- Para agregar filtros avanzados

---

#### **DireccionEntregaController.php**
```php
// Ubicación: app/Http/Controllers/DireccionEntregaController.php
```
**¿Qué hace?**
- CRUD de direcciones de entrega del usuario
- Gestiona múltiples direcciones por usuario
- Permite marcar dirección por defecto

**Métodos principales:**
- `index()` - Lista direcciones del usuario autenticado
- `store(Request $request)` - Crea nueva dirección
- `update(Request $request, $id)` - Actualiza dirección
- `destroy($id)` - Elimina dirección
- `marcarPorDefecto($id)` - Marca dirección como predeterminada

**Validaciones:**
```php
$request->validate([
    'nombre_completo' => 'required|string|max:255',
    'direccion' => 'required|string',
    'ciudad' => 'required|string|max:100',
    'estado' => 'required|string|max:100',
    'codigo_postal' => 'required|string|max:10',
    'telefono' => 'required|string|max:20',
]);
```

**Cuándo modificar:**
- Para agregar validación de código postal
- Para integrar con servicios de geolocalización
- Para calcular costos de envío por zona

---

### 📁 app/Http/Middleware

**Propósito:** Filtros que se ejecutan antes o después de las peticiones.

#### **AdminMiddleware.php**
```php
// Ubicación: app/Http/Middleware/AdminMiddleware.php
```
**¿Qué hace?**
- Verifica que el usuario autenticado sea administrador
- Protege rutas administrativas
- Redirige a usuarios no autorizados

**Lógica:**
```php
public function handle($request, Closure $next)
{
    if (!auth()->check() || !auth()->user()->es_admin) {
        abort(403, 'No tienes permisos de administrador');
    }
    
    return $next($request);
}
```

**Dónde se usa:**
- Rutas del panel administrativo
- Rutas de gestión de productos, categorías, órdenes

**Registrado en:** `app/Http/Kernel.php` o `bootstrap/app.php`

**Cuándo modificar:**
- Para agregar más niveles de permisos
- Para implementar roles y permisos granulares

---

### 📁 app/Models

**Propósito:** Modelos Eloquent que representan las tablas de la base de datos.

#### **User.php**
```php
// Ubicación: app/Models/User.php
```
**¿Qué hace?**
- Modelo de usuarios del sistema
- Extiende de Authenticatable (Laravel)
- Define relaciones con otras tablas

**Campos principales:**
- `name` - Nombre del usuario
- `email` - Email (único)
- `password` - Contraseña hasheada
- `es_admin` - Boolean que indica si es administrador

**Relaciones:**
```php
// Relación 1:1 con carrito
public function carrito() {
    return $this->hasOne(Carrito::class);
}

// Relación 1:N con órdenes
public function ordenes() {
    return $this->hasMany(Orden::class);
}

// Relación 1:N con direcciones
public function direccionesEntrega() {
    return $this->hasMany(DireccionEntrega::class);
}
```

**Cuándo modificar:**
- Para agregar campos de perfil (teléfono, avatar)
- Para implementar verificación de email
- Para agregar información de billing

---

#### **Producto.php**
```php
// Ubicación: app/Models/Producto.php
```
**¿Qué hace?**
- Modelo de productos
- Gestiona relaciones con categorías e imágenes
- Define accessors y mutators

**Campos principales:**
- `nombre` - Nombre del producto
- `slug` - URL amigable (único)
- `descripcion` - Descripción completa
- `precio` - Precio decimal
- `stock` - Cantidad disponible
- `categoria_id` - FK a categorías
- `activo` - Boolean de disponibilidad

**Relaciones:**
```php
// Pertenece a una categoría
public function categoria() {
    return $this->belongsTo(CategoriaProducto::class, 'categoria_id');
}

// Tiene muchas imágenes
public function imagenes() {
    return $this->hasMany(ImagenProducto::class);
}

// Imagen principal
public function imagenPrincipal() {
    return $this->hasOne(ImagenProducto::class)->where('es_principal', true);
}
```

**Accessors útiles:**
```php
// Obtener URL de imagen principal
public function getImagenPrincipalUrlAttribute() {
    $imagenPrincipal = $this->imagenPrincipal;
    
    if ($imagenPrincipal) {
        return asset('storage/' . $imagenPrincipal->ruta_imagen);
    }
    
    return asset('img/NoImagen.jpg'); // Imagen por defecto
}
```

**Cuándo modificar:**
- Para agregar campos (SKU, dimensiones, peso)
- Para implementar descuentos
- Para agregar variantes de productos

---

#### **CategoriaProducto.php**
```php
// Ubicación: app/Models/CategoriaProducto.php
```
**¿Qué hace?**
- Modelo de categorías de productos
- Gestiona slugs únicos
- Relaciona con productos

**Campos principales:**
- `nombre` - Nombre de la categoría
- `slug` - URL amigable
- `descripcion` - Descripción opcional
- `activo` - Boolean de disponibilidad

**Relaciones:**
```php
// Tiene muchos productos
public function productos() {
    return $this->hasMany(Producto::class, 'categoria_id');
}

// Solo productos activos
public function productosActivos() {
    return $this->hasMany(Producto::class, 'categoria_id')
                ->where('activo', true);
}
```

**Cuándo modificar:**
- Para implementar categorías jerárquicas (subcategorías)
- Para agregar iconos o imágenes a categorías
- Para ordenamiento personalizado

---

#### **ImagenProducto.php**
```php
// Ubicación: app/Models/ImagenProducto.php
```
**¿Qué hace?**
- Modelo de imágenes de productos
- Gestiona múltiples imágenes por producto
- Marca imagen principal

**Campos principales:**
- `producto_id` - FK al producto
- `ruta_imagen` - Ruta en storage
- `es_principal` - Boolean de imagen principal

**Relaciones:**
```php
// Pertenece a un producto
public function producto() {
    return $this->belongsTo(Producto::class);
}
```

**Accessor útil:**
```php
// URL completa de la imagen
public function getUrlAttribute() {
    return asset('storage/' . $this->ruta_imagen);
}
```

---

#### **Carrito.php**
```php
// Ubicación: app/Models/Carrito.php
```
**¿Qué hace?**
- Modelo del carrito de compras
- Relación 1:1 con usuario
- Contiene múltiples items

**Campos principales:**
- `user_id` - FK al usuario (único)

**Relaciones:**
```php
// Pertenece a un usuario
public function usuario() {
    return $this->belongsTo(User::class, 'user_id');
}

// Tiene muchos items
public function items() {
    return $this->hasMany(ItemCarrito::class);
}
```

**Métodos útiles:**
```php
// Calcular total del carrito
public function calcularTotal() {
    return $this->items->sum(function($item) {
        return $item->cantidad * $item->producto->precio;
    });
}

// Vaciar carrito
public function vaciar() {
    $this->items()->delete();
}
```

---

#### **ItemCarrito.php**
```php
// Ubicación: app/Models/ItemCarrito.php
```
**¿Qué hace?**
- Modelo de items individuales del carrito
- Relaciona productos con carritos
- Almacena cantidad

**Campos principales:**
- `carrito_id` - FK al carrito
- `producto_id` - FK al producto
- `cantidad` - Cantidad de unidades

**Relaciones:**
```php
// Pertenece a un carrito
public function carrito() {
    return $this->belongsTo(Carrito::class);
}

// Pertenece a un producto
public function producto() {
    return $this->belongsTo(Producto::class);
}
```

**Accessor útil:**
```php
// Subtotal del item
public function getSubtotalAttribute() {
    return $this->cantidad * $this->producto->precio;
}
```

---

#### **Orden.php**
```php
// Ubicación: app/Models/Orden.php
```
**¿Qué hace?**
- Modelo de órdenes de compra
- Gestiona estados de la orden
- Relaciona con detalles, pagos y direcciones

**Campos principales:**
- `user_id` - FK al usuario
- `numero_orden` - Número único de orden
- `fecha_orden` - Timestamp de creación
- `total` - Total de la orden
- `estado` - Estado actual (pendiente, procesando, enviado, entregado, cancelado)
- `metodo_pago` - Método usado (tarjeta, paypal)
- `direccion_entrega_id` - FK a dirección
- `notas_orden` - Notas del cliente

**Relaciones:**
```php
// Pertenece a un usuario
public function usuario() {
    return $this->belongsTo(User::class, 'user_id');
}

// Tiene muchos detalles (productos)
public function detalles() {
    return $this->hasMany(DetalleOrden::class);
}

// Tiene un pago
public function pago() {
    return $this->hasOne(Pago::class);
}

// Tiene una dirección de entrega
public function direccionEntrega() {
    return $this->belongsTo(DireccionEntrega::class, 'direccion_entrega_id');
}
```

**Scopes útiles:**
```php
// Filtrar por estado
public function scopeEstado($query, $estado) {
    return $query->where('estado', $estado);
}

// Órdenes del mes
public function scopeDelMes($query) {
    return $query->whereMonth('fecha_orden', now()->month)
                 ->whereYear('fecha_orden', now()->year);
}
```

---

#### **DetalleOrden.php**
```php
// Ubicación: app/Models/DetalleOrden.php
```
**¿Qué hace?**
- Detalla los productos de cada orden
- Almacena snapshot del precio al momento de compra

**Campos principales:**
- `orden_id` - FK a la orden
- `producto_id` - FK al producto
- `cantidad` - Cantidad comprada
- `precio_unitario` - Precio al momento de compra
- `subtotal` - Cantidad * precio_unitario

**Relaciones:**
```php
public function orden() {
    return $this->belongsTo(Orden::class);
}

public function producto() {
    return $this->belongsTo(Producto::class);
}
```

**Nota importante:**
- Guarda `precio_unitario` para preservar el precio histórico
- Aunque el precio del producto cambie, la orden mantiene el precio original

---

#### **Pago.php**
```php
// Ubicación: app/Models/Pago.php
```
**¿Qué hace?**
- Modelo de información de pagos
- Registra transacciones

**Campos principales:**
- `orden_id` - FK a la orden
- `monto` - Monto del pago
- `metodo_pago` - Método usado
- `estado_pago` - Estado (pendiente, completado, fallido)
- `fecha_pago` - Timestamp del pago

**Relaciones:**
```php
public function orden() {
    return $this->belongsTo(Orden::class);
}
```

**Cuándo modificar:**
- Para integrar pasarelas de pago reales
- Para agregar información de tarjeta (tokenizada)
- Para implementar reembolsos

---

#### **DireccionEntrega.php**
```php
// Ubicación: app/Models/DireccionEntrega.php
```
**¿Qué hace?**
- Modelo de direcciones de entrega
- Permite múltiples direcciones por usuario

**Campos principales:**
- `user_id` - FK al usuario
- `nombre_completo` - Nombre del destinatario
- `direccion` - Calle y número
- `ciudad` - Ciudad
- `estado` - Estado/Provincia
- `codigo_postal` - Código postal
- `telefono` - Teléfono de contacto
- `es_predeterminada` - Boolean de dirección por defecto

**Relaciones:**
```php
public function usuario() {
    return $this->belongsTo(User::class, 'user_id');
}

public function ordenes() {
    return $this->hasMany(Orden::class);
}
```

---

#### **Estadistica.php**
```php
// Ubicación: app/Models/Estadistica.php
```
**¿Qué hace?**
- Modelo para datos de estadísticas
- Almacena métricas agregadas para reportes

**Campos principales:**
- `orden_id` - FK a orden (opcional)
- `producto_id` - FK a producto (opcional)
- `user_id` - FK a usuario (opcional)
- `fecha` - Fecha del registro
- `tipo` - Tipo de estadística (venta, view, etc)
- `valor` - Valor numérico
- `metadata` - JSON con info adicional

**Cuándo usar:**
- Para almacenar métricas pre-calculadas
- Para datos de análisis histórico
- Para mejorar performance de reportes

---

### 📁 app/Providers

#### **AppServiceProvider.php**
```php
// Ubicación: app/Providers/AppServiceProvider.php
```
**¿Qué hace?**
- Configura servicios de la aplicación
- Registra bindings del contenedor
- Define configuraciones globales

**Métodos principales:**

1. **`register()`** - Registra servicios en el contenedor

2. **`boot()`** - Bootstrap de servicios
   - Configura paginación
   - Define relaciones Eloquent globales
   - Configura validadores personalizados

**Cuándo modificar:**
- Para registrar servicios personalizados
- Para configurar helpers globales
- Para agregar validadores custom

---

## 📂 Directorio Database

**Ubicación:** `/database`

### 📁 database/migrations

**Propósito:** Archivos de migración que crean/modifican la estructura de la base de datos.

#### Estructura de una migración:
```php
// Ejemplo: 2024_01_01_000001_create_productos_table.php
class CreateProductosTable extends Migration
{
    public function up() {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->decimal('precio', 10, 2);
            $table->timestamps();
        });
    }
    
    public function down() {
        Schema::dropIfExists('productos');
    }
}
```

#### Migraciones principales del proyecto:

1. **create_users_table** - Tabla de usuarios
2. **create_categorias_producto_table** - Categorías
3. **create_productos_table** - Productos
4. **create_imagenes_producto_table** - Imágenes de productos
5. **create_carritos_table** - Carritos
6. **create_items_carrito_table** - Items del carrito
7. **create_direcciones_entrega_table** - Direcciones
8. **create_ordenes_table** - Órdenes
9. **create_detalles_orden_table** - Detalles de órdenes
10. **create_pagos_table** - Pagos
11. **create_estadisticas_table** - Estadísticas

**Comandos útiles:**
```bash
# Crear migración
php artisan make:migration create_nombre_tabla_table

# Ejecutar migraciones
php artisan migrate

# Rollback última migración
php artisan migrate:rollback

# Refrescar toda la BD
php artisan migrate:fresh --seed
```

---

### 📁 database/seeders

**Propósito:** Archivos que insertan datos de prueba en la base de datos.

#### **DatabaseSeeder.php**
```php
// Ubicación: database/seeders/DatabaseSeeder.php
```
**¿Qué hace?**
- Ejecuta todos los seeders del proyecto
- Define el orden de ejecución

```php
public function run() {
    $this->call([
        UserSeeder::class,
        CategoriaProductoSeeder::class,
        ProductoSeeder::class,
    ]);
}
```

---

#### **UserSeeder.php**
```php
// Ubicación: database/seeders/UserSeeder.php
```
**¿Qué hace?**
- Crea usuarios de prueba
- Crea usuario administrador

**Usuarios creados:**
```php
// Administrador
User::create([
    'name' => 'Administrador',
    'email' => 'admin@catbox.com',
    'password' => bcrypt('admin123'),
    'es_admin' => true,
]);

// Usuario normal
User::create([
    'name' => 'Usuario Test',
    'email' => 'usuario@catbox.com',
    'password' => bcrypt('usuario123'),
    'es_admin' => false,
]);
```

---

#### **CategoriaProductoSeeder.php**
```php
// Ubicación: database/seeders/CategoriaProductoSeeder.php
```
**¿Qué hace?**
- Crea categorías de productos
- Genera slugs automáticamente

**Categorías creadas:**
- Nendoroid
- Photocards
- Llaveros

---

#### **ProductoSeeder.php**
```php
// Ubicación: database/seeders/ProductoSeeder.php
```
**¿Qué hace?**
- Crea productos de ejemplo
- Asigna categorías
- Crea imágenes de producto

**Cuándo modificar:**
- Para agregar más productos de prueba
- Para cambiar precios o stock iniciales
- Para probar con datos específicos

---

## 📂 Directorio Public

**Ubicación:** `/public`

**Propósito:** Punto de entrada de la aplicación. Archivos accesibles públicamente.

### Archivos y carpetas:

#### **index.php**
- Punto de entrada principal de Laravel
- NO modificar a menos que sepas lo que haces

#### **📁 img/**
```
public/img/
└── NoImagen.jpg   # Imagen placeholder para productos sin imagen
```

**¿Para qué sirve?**
- Imagen por defecto cuando un producto no tiene imagen
- Evita enlaces rotos

---

#### **📁 storage/** (link simbólico)
- Enlace a `storage/app/public`
- Creado con: `php artisan storage:link`
- Permite acceso público a archivos en storage

---

## 📂 Directorio Resources

**Ubicación:** `/resources`

### 📁 resources/views

**Propósito:** Plantillas Blade (vistas).

#### Estructura completa:

```
resources/views/
├── layouts/
│   └── app.blade.php           # Layout principal
├── auth/
│   ├── login.blade.php         # Formulario de login
│   └── register.blade.php      # Formulario de registro
├── admin/
│   ├── dashboard.blade.php     # Dashboard admin
│   ├── productos/
│   │   ├── index.blade.php     # Lista de productos
│   │   ├── create.blade.php    # Crear producto
│   │   ├── edit.blade.php      # Editar producto
│   │   └── show.blade.php      # Ver producto
│   ├── categorias/
│   │   ├── index.blade.php     # Lista de categorías
│   │   ├── create.blade.php    # Crear categoría
│   │   └── edit.blade.php      # Editar categoría
│   ├── ordenes/
│   │   ├── index.blade.php     # Lista de órdenes
│   │   └── show.blade.php      # Detalle de orden
│   └── estadisticas/
│       ├── dashboard.blade.php  # Dashboard estadísticas
│       ├── ventas.blade.php     # Análisis de ventas
│       ├── productos.blade.php  # Análisis de productos
│       └── clientes.blade.php   # Análisis de clientes
├── productos/
│   ├── index.blade.php         # Catálogo público
│   ├── show.blade.php          # Detalle de producto
│   ├── buscar.blade.php        # Resultados de búsqueda
│   └── categorias/
│       └── {slug}.blade.php    # Vista por categoría
├── carrito/
│   ├── index.blade.php         # Ver carrito
│   └── checkout.blade.php      # Proceso de pago
├── ordenes/
│   ├── index.blade.php         # Mis órdenes
│   └── show.blade.php          # Detalle de orden
├── usuario/
│   ├── dashboard.blade.php     # Dashboard usuario
│   └── direcciones/
│       └── index.blade.php     # Gestión de direcciones
└── landing.blade.php           # Página de inicio
```

---

### Vista por vista:

#### **layouts/app.blade.php**
```blade
<!-- Ubicación: resources/views/layouts/app.blade.php -->
```
**¿Qué hace?**
- Layout base que usan todas las demás vistas
- Contiene navbar, footer, scripts comunes
- Define secciones (`@yield`) para contenido

**Secciones principales:**
```blade
<!DOCTYPE html>
<html>
<head>
    @yield('title')
    <!-- Bootstrap CSS, iconos, etc -->
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">...</nav>
    
    <!-- Contenido dinámico -->
    <main>
        @yield('content')
    </main>
    
    <!-- Footer -->
    <footer>...</footer>
    
    <!-- Scripts -->
    @yield('scripts')
</body>
</html>
```

**Cuándo modificar:**
- Para cambiar diseño general
- Para agregar meta tags
- Para incluir nuevos scripts globales

---

#### **landing.blade.php**
```blade
<!-- Ubicación: resources/views/landing.blade.php -->
```
**¿Qué hace?**
- Página de inicio del sitio
- Muestra hero section
- Productos destacados
- Categorías populares

**Secciones típicas:**
- Hero banner
- Productos recientes (últimos 6)
- Categorías con enlaces
- Call to action

---

#### **auth/login.blade.php**
```blade
<!-- Ubicación: resources/views/auth/login.blade.php -->
```
**¿Qué hace?**
- Formulario de inicio de sesión
- Validación de credenciales
- Opción de "Remember me"
- Link a registro

---

#### **auth/register.blade.php**
```blade
<!-- Ubicación: resources/views/auth/register.blade.php -->
```
**¿Qué hace?**
- Formulario de registro de nuevos usuarios
- Validación de datos
- Creación de carrito automático

---

#### **productos/index.blade.php**
```blade
<!-- Ubicación: resources/views/productos/index.blade.php -->
```
**¿Qué hace?**
- Muestra catálogo completo de productos
- Grid responsivo (3-4 columnas)
- Paginación
- Cards de producto con imagen, nombre, precio

**Datos que recibe:**
- `$productos` - Colección paginada de productos

---

#### **productos/show.blade.php**
```blade
<!-- Ubicación: resources/views/productos/show.blade.php -->
```
**¿Qué hace?**
- Vista detallada de un producto individual
- Galería de imágenes
- Información completa
- Botón "Agregar al carrito"
- Productos relacionados

**Datos que recibe:**
- `$producto` - Modelo del producto
- `$productosRelacionados` - Productos de la misma categoría

---

#### **carrito/index.blade.php**
```blade
<!-- Ubicación: resources/views/carrito/index.blade.php -->
```
**¿Qué hace?**
- Muestra items del carrito en tabla
- Controles para actualizar cantidad
- Botón para eliminar items
- Resumen de totales
- Botón "Proceder al checkout"

**Datos que recibe:**
- `$carrito` - Modelo del carrito con items

**JavaScript incluido:**
- Actualización dinámica de cantidades
- Cálculo de subtotales en tiempo real

---

#### **carrito/checkout.blade.php**
```blade
<!-- Ubicación: resources/views/carrito/checkout.blade.php -->
```
**¿Qué hace?**
- Formulario de checkout
- Selección de dirección de entrega
- Método de pago
- Resumen de orden
- Notas adicionales

**Datos que recibe:**
- `$carrito` - Carrito con items
- `$direcciones` - Direcciones del usuario

---

#### **ordenes/index.blade.php**
```blade
<!-- Ubicación: resources/views/ordenes/index.blade.php -->
```
**¿Qué hace?**
- Lista todas las órdenes del usuario
- Muestra número de orden, fecha, total, estado
- Enlace a detalle de cada orden

**Datos que recibe:**
- `$ordenes` - Colección de órdenes del usuario

---

#### **ordenes/show.blade.php**
```blade
<!-- Ubicación: resources/views/ordenes/show.blade.php -->
```
**¿Qué hace?**
- Detalle completo de una orden
- Información de productos
- Dirección de entrega
- Estado de la orden
- Información de pago

**Datos que recibe:**
- `$orden` - Modelo de la orden con relaciones

---

#### **admin/dashboard.blade.php**
```blade
<!-- Ubicación: resources/views/admin/dashboard.blade.php -->
```
**¿Qué hace?**
- Dashboard principal del administrador
- Cards con KPIs (ventas, órdenes, usuarios, productos)
- Lista de órdenes pendientes
- Productos con stock bajo
- Top 5 productos más vendidos

**Datos que recibe:**
- `$totalVentas` - Total de ventas del mes
- `$totalOrdenes` - Cantidad de órdenes
- `$totalUsuarios` - Cantidad de usuarios
- `$totalProductos` - Cantidad de productos
- `$ordenesPendientes` - Órdenes pendientes
- `$productosStockBajo` - Productos con stock < 10
- `$topProductos` - Top 5 productos más vendidos

---

#### **admin/productos/index.blade.php**
```blade
<!-- Ubicación: resources/views/admin/productos/index.blade.php -->
```
**¿Qué hace?**
- Lista todos los productos (tabla)
- Filtros de búsqueda
- Acciones: Editar, Eliminar, Activar/Desactivar
- Indicador de stock
- Link a crear nuevo producto

**Datos que recibe:**
- `$productos` - Colección paginada de productos

---

#### **admin/productos/create.blade.php**
```blade
<!-- Ubicación: resources/views/admin/productos/create.blade.php -->
```
**¿Qué hace?**
- Formulario para crear nuevo producto
- Campos: nombre, descripción, precio, stock, categoría
- Upload múltiple de imágenes
- Validación con Bootstrap

**Datos que recibe:**
- `$categorias` - Todas las categorías disponibles

---

#### **admin/productos/edit.blade.php**
```blade
<!-- Ubicación: resources/views/admin/productos/edit.blade.php -->
```
**¿Qué hace?**
- Formulario para editar producto existente
- Pre-rellena campos con datos actuales
- Gestión de imágenes (agregar, eliminar, cambiar principal)

**Datos que recibe:**
- `$producto` - Producto a editar
- `$categorias` - Todas las categorías

---

#### **admin/estadisticas/dashboard.blade.php**
```blade
<!-- Ubicación: resources/views/admin/estadisticas/dashboard.blade.php -->
```
**¿Qué hace?**
- Dashboard general de estadísticas
- Gráfico de ventas por mes (últimos 6 meses)
- Gráfico de productos más vendidos
- Gráfico de ventas por categoría
- Comparación con mes anterior

**Datos que recibe:**
- `$ventasPorMes` - Array con labels y datos
- `$productosMasVendidos` - Top productos
- `$ventasPorCategoria` - Ventas agrupadas

**Charts incluidos:**
- Line Chart (ventas mensuales)
- Bar Chart (productos)
- Pie Chart (categorías)

---

#### **admin/estadisticas/ventas.blade.php**
```blade
<!-- Ubicación: resources/views/admin/estadisticas/ventas.blade.php -->
```
**¿Qué hace?**
- Análisis detallado de ventas
- Ventas por día del mes
- Ventas por día de la semana
- Ventas por hora del día
- Métodos de pago más usados

**Charts incluidos:**
- Line Chart (ventas diarias)
- Bar Chart (días de la semana)
- Bar Chart (horas del día)
- Doughnut Chart (métodos de pago)

---

#### **admin/estadisticas/productos.blade.php**
```blade
<!-- Ubicación: resources/views/admin/estadisticas/productos.blade.php -->
```
**¿Qué hace?**
- Top 10 productos más vendidos
- Lista de productos sin ventas
- Productos con stock bajo
- Rendimiento por categoría
- Valor total del inventario

**Charts incluidos:**
- Horizontal Bar Chart (top productos)
- Pie Chart (ventas por categoría)

---

#### **admin/estadisticas/clientes.blade.php**
```blade
<!-- Ubicación: resources/views/admin/estadisticas/clientes.blade.php -->
```
**¿Qué hace?**
- Top 10 clientes por monto gastado
- Clientes nuevos vs. recurrentes
- Ticket promedio por cliente
- Segmentación por cantidad de compras

**Charts incluidos:**
- Bar Chart (top clientes)
- Pie Chart (nuevos vs recurrentes)
- Bar Chart (segmentación)

---

## 📂 Directorio Routes

**Ubicación:** `/routes`

### **web.php**
```php
// Ubicación: routes/web.php
```
**¿Qué hace?**
- Define todas las rutas web de la aplicación
- Agrupa rutas por funcionalidad
- Aplica middleware de autenticación y admin

**Estructura de rutas:**

#### **Rutas Públicas**
```php
// Página de inicio
Route::get('/', [ProductoController::class, 'landing'])->name('landing');

// Autenticación (Laravel Breeze/UI)
Auth::routes();

// Catálogo de productos
Route::get('/productos', [ProductoController::class, 'index'])->name('productos.index');
Route::get('/productos/{id}', [ProductoController::class, 'show'])->name('productos.show');
Route::get('/buscar', [ProductoController::class, 'buscar'])->name('productos.buscar');

// Categorías
Route::get('/categoria/{slug}', [CategoriaController::class, 'show'])->name('categorias.show');
```

#### **Rutas Protegidas (Autenticación requerida)**
```php
Route::middleware(['auth'])->group(function () {
    // Dashboard de usuario
    Route::get('/usuario/dashboard', [UserController::class, 'dashboard'])->name('usuario.dashboard');
    
    // Carrito
    Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito.index');
    Route::post('/carrito/agregar', [CarritoController::class, 'agregar'])->name('carrito.agregar');
    Route::patch('/carrito/{item}', [CarritoController::class, 'actualizar'])->name('carrito.actualizar');
    Route::delete('/carrito/{item}', [CarritoController::class, 'eliminar'])->name('carrito.eliminar');
    
    // Checkout y Órdenes
    Route::get('/checkout', [OrdenController::class, 'checkout'])->name('ordenes.checkout');
    Route::post('/checkout', [OrdenController::class, 'procesarCheckout'])->name('ordenes.procesar');
    Route::get('/ordenes', [OrdenController::class, 'index'])->name('ordenes.index');
    Route::get('/ordenes/{id}', [OrdenController::class, 'show'])->name('ordenes.show');
    
    // Direcciones de entrega
    Route::resource('direcciones', DireccionEntregaController::class);
});
```

#### **Rutas Admin (Middleware admin)**
```php
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    // Dashboard
    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
    
    // Productos
    Route::resource('productos', ProductoController::class)->names([
        'index' => 'admin.productos.index',
        'create' => 'admin.productos.create',
        // ...
    ]);
    Route::post('/productos/{id}/toggle', [ProductoController::class, 'toggleEstado'])->name('admin.productos.toggle');
    
    // Categorías
    Route::resource('categorias', CategoriaProductoController::class)->names([
        'index' => 'admin.categorias.index',
        // ...
    ]);
    
    // Órdenes
    Route::get('/ordenes', [OrdenController::class, 'admin_index'])->name('admin.ordenes.index');
    Route::patch('/ordenes/{id}', [OrdenController::class, 'actualizar'])->name('admin.ordenes.actualizar');
    
    // Estadísticas
    Route::prefix('estadisticas')->group(function () {
        Route::get('/dashboard', [EstadisticaController::class, 'dashboard'])->name('admin.estadisticas.dashboard');
        Route::get('/ventas', [EstadisticaController::class, 'ventas'])->name('admin.estadisticas.ventas');
        Route::get('/productos', [EstadisticaController::class, 'productos'])->name('admin.estadisticas.productos');
        Route::get('/clientes', [EstadisticaController::class, 'clientes'])->name('admin.estadisticas.clientes');
    });
});
```

**Cuándo modificar:**
- Al agregar nuevas funcionalidades
- Para cambiar URLs
- Para aplicar nuevos middleware
- Para versionar API

---

## 📂 Directorio Storage

**Ubicación:** `/storage`

**Propósito:** Almacenamiento de archivos generados por la aplicación.

### Estructura:

```
storage/
├── app/
│   ├── public/              # Archivos públicos (accesibles vía storage link)
│   │   └── productos/       # Imágenes de productos
│   └── private/             # Archivos privados
├── framework/
│   ├── cache/               # Caché de la aplicación
│   ├── sessions/            # Sesiones de usuarios
│   └── views/               # Vistas compiladas de Blade
└── logs/
    └── laravel.log          # Logs de la aplicación
```

### 📁 storage/app/public/productos

**¿Qué contiene?**
- Imágenes de productos subidas por admin
- Nombradas con timestamp para evitar duplicados
- Formatos: jpg, jpeg, png, gif

**Acceso:**
- Mediante link simbólico: `public/storage`
- URL: `{{ asset('storage/productos/imagen.jpg') }}`

**Cuándo limpiar:**
- Al eliminar productos
- Para liberar espacio
- Durante desarrollo con `php artisan migrate:fresh`

---

## 📄 Archivos de Configuración

### **.env**
```bash
# Ubicación: /.env
```
**¿Qué es?**
- Archivo de variables de entorno
- NO se versiona en Git (.gitignore)
- Contiene credenciales sensibles

**Variables importantes:**
```env
APP_NAME=Catbox
APP_ENV=local          # local, production
APP_DEBUG=true         # false en producción
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=catbox
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
# ... más configuraciones
```

---

### **.env.example**
```bash
# Ubicación: /.env.example
```
**¿Qué es?**
- Plantilla del archivo .env
- SÍ se versiona en Git
- Sin valores sensibles

**Uso:**
```bash
cp .env.example .env
```

---

### **composer.json**
```json
// Ubicación: /composer.json
```
**¿Qué es?**
- Define dependencias PHP del proyecto
- Configuración de autoload
- Scripts de Composer

**Dependencias principales:**
```json
{
    "require": {
        "php": "^8.2",
        "laravel/framework": "^11.0",
        "laravel/tinker": "^2.9"
    }
}
```

**Comandos:**
```bash
# Instalar dependencias
composer install

# Actualizar dependencias
composer update

# Agregar nueva dependencia
composer require vendor/package
```

---

### **package.json**
```json
// Ubicación: /package.json
```
**¿Qué es?**
- Define dependencias JavaScript/Node
- Scripts de npm

**Dependencias típicas:**
```json
{
    "devDependencies": {
        "bootstrap": "^5.3.0",
        "vite": "^5.0.0"
    }
}
```

**Comandos:**
```bash
# Instalar dependencias
npm install

# Compilar assets (desarrollo)
npm run dev

# Compilar assets (producción)
npm run build
```

---

## 📝 Notas Personales

### 🔍 Flujo de Datos Completo

**Usuario hace una compra:**

1. **Explorar productos** → `ProductoController@index`
2. **Ver detalle** → `ProductoController@show`
3. **Agregar al carrito** → `CarritoController@agregar`
   - Valida stock
   - Crea/actualiza ItemCarrito
4. **Ver carrito** → `CarritoController@index`
5. **Ir a checkout** → `OrdenController@checkout`
6. **Procesar compra** → `OrdenController@procesarCheckout`
   - Crea Orden
   - Crea DetalleOrden por cada producto
   - Reduce stock
   - Crea Pago
   - Asigna DireccionEntrega
   - Vacía carrito
   - Genera Estadistica
7. **Ver orden** → `OrdenController@show`

---

### 🎨 Mejoras Sugeridas

#### Corto plazo:
- [ ] Agregar validación de email único en registro
- [ ] Implementar soft deletes en productos
- [ ] Agregar filtro de búsqueda en órdenes admin
- [ ] Mejorar mensajes de error con traducciones

#### Mediano plazo:
- [ ] Sistema de reviews y ratings
- [ ] Wishlist de productos
- [ ] Notificaciones por email (Laravel Notifications)
- [ ] Exportar reportes a PDF (DomPDF o TCPDF)

#### Largo plazo:
- [ ] API RESTful con Laravel Sanctum
- [ ] Multi-idioma (Laravel Lang)
- [ ] Multi-moneda
- [ ] Integración con pasarelas de pago reales

---

### 🐛 Bugs Conocidos / Por Resolver

1. **Stock negativo:** Si dos usuarios compran simultáneamente el último item, el stock puede volverse negativo. **Solución:** Usar transacciones de BD y bloqueos.

2. **Imágenes huérfanas:** Al eliminar productos, las imágenes en storage no se borran. **Solución:** Agregar lógica de eliminación en ProductoController@destroy.

3. **Carrito persistente:** El carrito no expira, puede acumular items antiguos. **Solución:** Agregar expiración de items o limpiar carritos viejos con un comando artisan.

---

### 📊 Queries Optimizables

#### CarritoController@index
```php
// ANTES (N+1 queries)
$items = $carrito->items;
foreach ($items as $item) {
    echo $item->producto->nombre; // Query por cada item
}

// DESPUÉS (Eager loading)
$items = $carrito->items()->with('producto.imagenPrincipal')->get();
```

#### ProductoController@index
```php
// ANTES
$productos = Producto::where('activo', true)->paginate(12);

// DESPUÉS (Con eager loading)
$productos = Producto::where('activo', true)
    ->with(['categoria', 'imagenPrincipal'])
    ->paginate(12);
```

---

### 🔐 Seguridad

#### Validar siempre:
1. **Input del usuario** - Usar `$request->validate()`
2. **Autenticación** - Middleware `auth`
3. **Autorización** - Políticas o Gates
4. **CSRF** - Token en formularios
5. **SQL Injection** - Eloquent previene automáticamente
6. **XSS** - Blade escapa automáticamente con `{{ }}`

#### Puntos sensibles:
- Upload de imágenes (validar tipo y tamaño)
- Actualización de stock (validar cantidad > 0)
- Procesamiento de pagos (nunca almacenar CVV)

---

### 🚀 Comandos Útiles

```bash
# Desarrollo
php artisan serve                    # Servidor local
php artisan migrate:fresh --seed     # Resetear BD con datos
php artisan route:list               # Ver todas las rutas
php artisan tinker                   # REPL de Laravel

# Producción
php artisan optimize                 # Optimizar caches
php artisan config:cache             # Cachear configuración
php artisan route:cache              # Cachear rutas
php artisan view:cache               # Cachear vistas

# Limpieza
php artisan cache:clear              # Limpiar caché
php artisan config:clear             # Limpiar config cache
php artisan view:clear               # Limpiar views cache
php artisan route:clear              # Limpiar routes cache
```

---

### 📦 Estructura de Carpetas Recomendada para Nuevas Features

Si agregas una nueva funcionalidad (ej: Reviews):

```
app/
├── Http/
│   └── Controllers/
│       └── ReviewController.php
├── Models/
│   └── Review.php

database/
├── migrations/
│   └── create_reviews_table.php
└── seeders/
    └── ReviewSeeder.php

resources/
└── views/
    └── reviews/
        ├── index.blade.php
        └── create.blade.php
```

---

### 💡 Tips y Trucos

#### Debugging:
```php
// Ver queries ejecutadas
DB::enableQueryLog();
// ... código ...
dd(DB::getQueryLog());

// Dump de variables
dd($variable);       // Dump and die
dump($variable);     // Dump sin detener

// Log personalizado
Log::info('Mensaje', ['dato' => $valor]);
```

#### Accessors y Mutators útiles:
```php
// Accessor: formatear precio
public function getPrecioFormateadoAttribute() {
    return '$' . number_format($this->precio, 2);
}

// Mutator: convertir a mayúsculas antes de guardar
public function setNombreAttribute($value) {
    $this->attributes['nombre'] = strtoupper($value);
}
```

#### Scopes útiles:
```php
// En el modelo Producto
public function scopeActivos($query) {
    return $query->where('activo', true);
}

public function scopeConStock($query) {
    return $query->where('stock', '>', 0);
}

// Uso
$productos = Producto::activos()->conStock()->get();
```

---

## 🎓 Recursos de Aprendizaje

- **Laravel Docs:** https://laravel.com/docs
- **Laracasts:** https://laracasts.com (tutoriales en video)
- **Laravel News:** https://laravel-news.com
- **Laravel Daily:** https://laraveldaily.com

---

## ✅ Checklist de Deploy

Antes de subir a producción:

- [ ] Cambiar `APP_ENV=production` en `.env`
- [ ] Configurar `APP_DEBUG=false`
- [ ] Generar nueva `APP_KEY`
- [ ] Configurar BD de producción
- [ ] Ejecutar migraciones: `php artisan migrate --force`
- [ ] Optimizar: `php artisan optimize`
- [ ] Configurar permisos: `chmod -R 775 storage bootstrap/cache`
- [ ] Configurar HTTPS
- [ ] Cambiar credenciales de admin
- [ ] Configurar backups automáticos
- [ ] Configurar logs (Papertrail, Sentry)
- [ ] Probar todas las funcionalidades críticas

---

<div align="center">

### 📖 Fin de la Documentación Personal

**Esta documentación se actualiza a medida que el proyecto evoluciona.**

Última actualización: Febrero 2026

</div>