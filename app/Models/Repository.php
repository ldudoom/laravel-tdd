<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Repository extends Model
{
    use HasFactory;

    // Configuramos el modelo para poder recibir registros de forma masiva, agregando lo siguiente:
    protected $fillable = [
        'url',
        'description',
    ];

    public function user(){
        return $this->belongsTo(User::class);
    }
}
