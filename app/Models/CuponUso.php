<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuponUso extends Model
{
    protected $table = 'cupon_uso';
    protected $fillable = ['cupon_id', 'user_id', 'orden_id'];

    public function cupon() { return $this->belongsTo(Cupon::class); }
    public function user()  { return $this->belongsTo(User::class); }
    public function orden() { return $this->belongsTo(Orden::class); }
}