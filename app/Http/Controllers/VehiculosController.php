<?php

namespace App\Http\Controllers;

use App\Models\Usuarios;
use App\Models\Vehiculos;
use ConfigModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehiculosController extends Controller
{

    public function __construct()
    {
        Vehiculos::crearTabla();
    }

    public function getAsientosDisponibles($dni)
    {
        return Vehiculos::getAsientosLibres($dni);
    }

    public function getVehiculos()
    {
        $query = "SELECT v.*, CONCAT(u.nombre, ' ' , u.apellido_paterno, ' ' , u.apellido_materno) as nombre_conductor, u.celular FROM vehiculos as v
        INNER JOIN usuarios as u ON v.dni = u.dni
        WHERE u.rol = 'CONDUCTOR'
        ";
        $vehiculos = DB::select($query);
        return response()->json($vehiculos, 200);
    }

    public function getVehiculoByDNI($dni)
    {
        $vehiculo = Vehiculos::where('dni', $dni)->get()->first();
        return response()->json($vehiculo, 200);
    }


    public function getVehiculoByName($nombre)
    {
        $vehiculo = Vehiculos::where('nombre_conductor', $nombre)->get()->first();
        return response()->json($vehiculo, 200);
    }
    public function deleteVehiculos($id)
    {
        try {
            if (!Vehiculos::where('id', $id)->exists()) {
                return ConfigModel::exception(400, 'El vehículo no existe');
            }
            Vehiculos::where('id', $id)->delete();
            return ConfigModel::withJsonResponse(200, 'Vehículo eliminado correctamente');
        } catch (\Throwable $e) {
            return ConfigModel::withJsonResponse(400, $e->getMessage());
        }
    }

    public function addVehiculo(Request $request)
    {
        try {
            $data = $request->json()->all();
            $placa = $data['placa'];
            $num_asientos = $data['num_asientos'];
            $dni = $data['dni'];


            if (empty($placa) || empty($num_asientos) || empty($dni)) {
                return ConfigModel::exception(400, 'Debe completar todos los campos para registrar el vehículo correctamente');
            }

            if (Vehiculos::where('placa', $placa)->exists()) {
                return ConfigModel::exception(400, 'La placa ya existe, ingrese otra placa');
            }

            if (Vehiculos::where('dni', $dni)->exists()) {
                return ConfigModel::exception(400, 'El dni ya existe, ingrese otro dni');
            }

            if (Usuarios::where('dni', $dni)
                ->where('rol', '!=', 'CONDUCTOR')
                ->exists()
            ) {
                return ConfigModel::exception(400, 'Este DNI no puede ser registrado con este rol. Debe ser un conductor');
            }

            Vehiculos::create([
                'placa' => $placa,
                'num_asientos' => $num_asientos,
                'dni' => $dni,

            ]);
            return ConfigModel::withJsonResponse(200, "Vehículo creado correctamente");
        } catch (\Throwable $e) {
            return ConfigModel::withJsonResponse(400, $e->getMessage());
        }
    }
    public function getIncidenciasConductor($dni)
    {
        $query = "SELECT u.dni, u.celular, CONCAT(u.nombre, ' ', IFNULL(u.apellido_paterno, ''), ' ', IFNULL(u.apellido_materno, '')) AS nombre_completo, COUNT(i.dni) AS cant_incidencias FROM usuarios u LEFT JOIN incidencias i ON u.dni = i.dni WHERE u.dni = ? GROUP BY u.dni, u.celular, u.nombre, u.apellido_paterno, u.apellido_materno;";

        $result = DB::select($query, [$dni])[0];
        return response()->json($result);
    }

    public function getIsActiveTravel(int $dni, String $estado)
    {
        try {
            $query = "";
            if ($estado == "" || empty($estado)) {
                return ConfigModel::exception(400, 'El estado no existe o no es valido');
            }
            if ($estado == "PASAJERO") {
                $query = "SELECT * FROM reportes_viaje WHERE dni_usuario = ? AND estado = 0";
            }
            if ($estado == "CONDUCTOR") {
                $query = "SELECT * FROM reportes_viaje WHERE dni_conductor = ? AND estado = 0";
            }

            $result = DB::select($query, [$dni])[0];
            if (empty($result)) {
                return response()->json([
                    'value'  => false,
                    'dni_usuario' => $result->dni_usuario,
                    'dni_conductor' => $result->dni_conductor,
                    'address' => $result->direccion_final ?? "No existe dirección final",
                    'lng_final' => (string) $result->lng_final,
                    'lat_final' => (string) $result->lat_final,
                ]);
            }
            return response()->json([
                'value'  => true,
                'dni_usuario' => $result->dni_usuario,
                'dni_conductor' => $result->dni_conductor,
                'address' => $result->direccion_final ?? "No existe dirección final",
                'lng_final' => (string) $result->lng_final,
                'lat_final' => (string) $result->lat_final,
            ]);
        } catch (\Throwable $th) {
            return ConfigModel::withJsonResponse(400, $th->getMessage());
        }
    }

    // Función para guardar o actualizar Vehículo Solicitud
    public function guardarVehiculoSolicitud(Request $request)
    {
        return Vehiculos::guardarVehiculoSolicitud($request);
    }

    // Función para crear Reporte de Viaje
    public function crearReporteViaje(Request $request)
    {
        return Vehiculos::crearReporteViaje($request);
    }

    // Función para actualizar las coordenadas finales en un Reporte de Viaje
    public function actualizarReporteViajeFinalReal(Request $request)
    {
        return Vehiculos::actualizarReporteViajeFinalReal($request);
    }
}
