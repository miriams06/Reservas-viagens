<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Viagem extends Model
{
    use HasFactory;

    protected $table = 'viagens';
    
    protected $fillable = [
        'destino',
        'data_partida',
        'data_regresso',
        'preco'
    ];

    /**
     * Obter todas as reservas para esta viagem.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function reservas()
    {
        return $this->hasMany(Reserva::class);
    }
}

