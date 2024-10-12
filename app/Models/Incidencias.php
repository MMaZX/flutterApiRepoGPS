<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Incidencias extends Model
{
    // Definimos el nombre de la tabla si es diferente al plural del modelo
    protected $table = 'incidencias';

    // Especificamos los campos que son asignables en masa (mass assignable)
    protected $fillable = [
        'dni',
        'comentario',
        'incidenciasArray',
        'dateTime',
        'estado'
    ];

    // Crear la tabla si no existe
    public static function crearTabla()
    {
        DB::statement("
            CREATE TABLE IF NOT EXISTS incidencias (
                id INT AUTO_INCREMENT PRIMARY KEY,
                dni VARCHAR(255) NOT NULL,
                comentario TEXT NOT NULL,
                incidenciasArray TEXT NOT NULL, -- Almacenamos el texto separado por comas
                dateTime DATETIME NOT NULL,
                estado TINYINT(1) DEFAULT 0, -- Solo puede ser 0 o 1
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            );
");
    }
}
