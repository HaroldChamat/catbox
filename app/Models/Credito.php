<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Credito extends Model
{
    protected $table = 'creditos';

    protected $fillable = [
        'user_id', 'devolucion_id', 'monto', 'saldo', 'usado',
    ];

    protected $casts = [
        'usado' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function devolucion()
    {
        return $this->belongsTo(Devolucion::class);
    }

    public function tieneDisponible()
    {
        return $this->saldo > 0 && !$this->usado;
    }
}