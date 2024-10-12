<?php

namespace App\Models;

use ConfigModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Dashboard extends Model
{
    use HasFactory;
    public static function getData()
    {
        try {
            $lista = [];

            $query_usuarios = "SELECT COUNT(*) as total_usuarios FROM usuarios";
            $query_vehiculos = "SELECT COUNT(*) as total_vehiculos FROM vehiculos";
            $query_incidencias = "SELECT COUNT(*) as total_incidencias FROM incidencias i";
            $query_incidencias_model = Incidencias::all();
            $query_vehiculos_rojos = [];
            $usuarios = DB::select($query_usuarios);
            $vehiculos = DB::select($query_vehiculos);
            $incidencias = DB::select($query_incidencias);

            try {
                $query_vehiculos_rojos = DB::table('incidencias')
                    ->join('vehiculos', 'incidencias.dni', '=', 'vehiculos.dni') // Asegúrate de que la relación sea correcta
                    ->select('incidencias.dni', 'vehiculos.placa', DB::raw('COUNT(*) as total_incidencias'))
                    ->groupBy('incidencias.dni', 'vehiculos.placa') // Agrupamos también por la placa
                    ->get();
            } catch (\Throwable $th) {
                // Si hay un error en esta consulta, devolvemos un array vacío
                $query_vehiculos_rojos = [];
            }

            return [
                "total_usuarios" => (string) $usuarios[0]->total_usuarios,
                "total_vehiculos" => (string) $vehiculos[0]->total_vehiculos,
                "total_incidencias" => (string) $incidencias[0]->total_incidencias,
                "ultimas_incidencias" => $query_incidencias_model,
                "total_vehiculos_rojos" => $query_vehiculos_rojos
            ];
        } catch (\Throwable $th) {
            return ConfigModel::withJsonResponse(400, "Ha ocurrido un error ene la consulta");
        }
    }
}
