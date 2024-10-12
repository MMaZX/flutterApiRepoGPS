<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Usuarios extends Model
{
    use HasFactory;

    protected $table = 'usuarios'; // Nombre de la tabla
    protected $fillable = [
        'nombre',
        'apellido_paterno',
        'apellido_materno',
        'dni',
        'celular',
        'usuario',
        'clave',
        'rol',
    ];

    // Crear la tabla si no existe
    public static function crearTabla()
    {
        DB::statement("
                CREATE TABLE IF NOT EXISTS usuarios (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    nombre VARCHAR(255) NOT NULL,
                    apellido_paterno VARCHAR(255) NOT NULL,
                    apellido_materno VARCHAR(255) NOT NULL,
                    dni VARCHAR(20) NOT NULL UNIQUE,
                    celular VARCHAR(15) NOT NULL UNIQUE,
                    usuario VARCHAR(255) NOT NULL UNIQUE,
                    clave VARCHAR(255) NOT NULL,
                    rol ENUM('PASAJERO', 'CONDUCTOR', 'ADMIN') NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )
            ");
    }
}
