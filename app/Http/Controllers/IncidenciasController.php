<?php

namespace App\Http\Controllers;

use App\Models\Incidencias;
use ConfigModel;
use DateTime;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IncidenciasController extends Controller
{
    public function __construct()
    {
        Incidencias::crearTabla();
    }

    public function listarIncidencias()
    {
        try {
            $incidencias = DB::select("SELECT i.*,  CONCAT(u.nombre, ' ' , u.apellido_paterno, ' ' , u.apellido_materno) AS nombre_usuario , u.celular as celular_usuario FROM incidencias i
        INNER JOIN usuarios u ON i.dni = u.dni
         ");
            return response()->json($incidencias);
        } catch (\Throwable $th) {
            return [];
        }
    }

    // public function listarIncidencias()
    // {
    //     $incidencias = Incidencias::all();
    //     return response()->json($incidencias);
    // }

    public function crearIncidencias(Request $request)
    {
        try {
            $data = $request->json()->all();
            $dni = $data['dni'];
            $comentario = $data['comentario'] ?? '';
            $incidenciasArray = $data['incidenciasArray'] ?? '[]';
            $dateTime = new DateTime();

            Incidencias::create([
                'dni' => $dni,
                'comentario' => $comentario,
                'incidenciasArray' => $incidenciasArray,
                'dateTime' => $dateTime->format('Y-m-d H:i:s'),
                'estado' => 0
            ]);
            return ConfigModel::withJsonResponse(200, "Registro creado correctamente");
        } catch (\Throwable $th) {
            return ConfigModel::withJsonResponse(500, $th->getMessage());
        }
    }
}
