<?php

namespace App\Models;

use ConfigModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;

class Vehiculos extends Model
{
    // use HasFactory;
    protected $table = 'vehiculos';

    protected $fillable = [
        'placa',
        'num_asientos',
        'dni',
    ];

    public static function getAsientosLibres($dni)
    {
        $query = "SELECT * FROM vehiculos_solicitud WHERE dni_usuario = ?";
        $vehiculos = DB::select($query, [$dni]);

        if (empty($vehiculos) || count($vehiculos) == 0) {
            return response()->json([
                'dni_usuario' => $dni,
                'num_asientos_activos' => 0,
                'estado' => 0,
                'created_at' => null,
                'updated_at' => null
            ]);
        }

        // Si hay resultados, simplemente devuelve el primer registro encontrado
        return response()->json($vehiculos[0]);
    }

    public static  function guardarVehiculoSolicitud($request)
    {
        try {
            // Validamos los datos recibidos
            $data = $request->json()->all();
            $dni_usuario = $data['dni_usuario'];
            $num_asientos_activos = $data['num_asientos_activos'];
            $estado = $data['estado'] ?? 0;

            // Verificamos si ya existe un registro con el dni_usuario
            $vehiculoSolicitud = DB::table('vehiculos_solicitud')
                ->where('dni_usuario', $dni_usuario)
                ->first();

            if ($vehiculoSolicitud) {
                // Si existe, actualizamos el registro
                if ($vehiculoSolicitud->estado == 0) {
                    $num_asientos_activos = $num_asientos_activos + $vehiculoSolicitud->num_asientos_activos;
                }

                DB::table('vehiculos_solicitud')
                    ->where('dni_usuario', $dni_usuario)
                    ->update([
                        'num_asientos_activos' => $num_asientos_activos,
                        'estado' => $estado,
                        'updated_at' => now()
                    ]);
                return response()->json(['message' => 'Vehículo actualizado correctamente.']);
            } else {
                // Si no existe, creamos uno nuevo
                DB::table('vehiculos_solicitud')->insert([
                    'dni_usuario' => $dni_usuario,
                    'num_asientos_activos' => $num_asientos_activos,
                    'estado' => $estado,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                return response()->json(['message' => 'Vehículo creado correctamente.']);
            }
        } catch (\Throwable $th) {
            return ConfigModel::withJsonResponse(400, $th->getMessage());
        }
    }

    public static function crearReporteViaje($request)
    {     // Validamos los datos recibidos
        try {
            $data = $request->json()->all();
            $dni_usuario = $data['dni_usuario'];
            $dni_conductor = $data['dni_conductor'];
            $num_asientos = $data['num_asientos'];
            $lng_inicial = $data['lng_inicial'];
            $lat_inicial = $data['lat_inicial'];
            $lng_final = $data['lng_final'];
            $lat_final = $data['lat_final'];
            $direccion_final = $data['direccion_final'];
            $estado = 0;

            // Insertamos el nuevo reporte
            DB::table('reportes_viaje')->insert([
                'dni_usuario' => $dni_usuario,
                'dni_conductor' => $dni_conductor,
                'num_asientos' => $num_asientos,
                'lng_inicial' => $lng_inicial,
                'lat_inicial' => $lat_inicial,
                'lng_final' => $lng_final,
                'lat_final' => $lat_final,
                'direccion_final' => $direccion_final,
                'estado' => $estado,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json(['message' => 'Reporte de viaje creado correctamente.']);
        } catch (\Throwable $th) {
            return ConfigModel::withJsonResponse(400, $th->getMessage());
        }
    }

    public static function actualizarReporteViajeFinalReal($request)
    {
        try {
            // Validamos los datos recibidos
            $data = $request->json()->all();
            $dni_usuario = $data['dni_usuario'];
            $lng_final_real = $data['lng_final_real'];
            $lat_final_real = $data['lat_final_real'];
            $valoracion = $data['valoracion'];


            // EL DNI QUE VAMOS A PASAR ES EL DNI DEL USUARIO DEL VIAJE
            $responseReporteViaje = DB::table('reportes_viaje')
                ->where(['dni_usuario' => $dni_usuario, 'estado' => 0,])
                ->first();
            if ($responseReporteViaje == null || empty($responseReporteViaje)) {
                return ConfigModel::exception(400, "El reporte de viaje ya fue finalizado.");
            }

            $dniConductor = $responseReporteViaje->dni_conductor;
            $responseVehiculosSolicitud = DB::table('vehiculos_solicitud')
                ->where('dni_usuario', $dniConductor)
                ->first();
            if ($responseVehiculosSolicitud == null || empty($responseVehiculosSolicitud)) {
                return ConfigModel::exception(400, "El vehículo no existe, DNI: $dniConductor");
            }

            $a = intval($responseVehiculosSolicitud->num_asientos_activos);
            $b = intval($responseReporteViaje->num_asientos);
            $totalResta = $a - $b;
            if ($responseReporteViaje) {
                DB::beginTransaction();
                DB::table('reportes_viaje')
                    ->where('dni_usuario', $dni_usuario)
                    ->update([
                        'lng_final_real' => $lng_final_real,
                        'lat_final_real' => $lat_final_real,
                        'updated_at' => now(),
                        'valoracion' => $valoracion,
                        'estado' => 1,
                    ]);
                DB::table('vehiculos_solicitud')
                    ->where('dni_usuario', $dniConductor)
                    ->update([
                        'num_asientos_activos' => $totalResta,
                        'estado' => 1,
                        'updated_at' => now(),
                    ]);
                DB::commit();
                return response()->json(['message' => 'Reporte de viaje actualizado correctamente.']);
            } else {
                return ConfigModel::exception(400, "El reporte de viaje no existe, DNI: $dni_usuario");
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return ConfigModel::withJsonResponse(400, $th->getMessage());
        }
    }


    public static function crearTabla()
    {
        try {
            DB::statement(
                "CREATE TABLE IF NOT EXISTS vehiculos(
            id INT AUTO_INCREMENT PRIMARY KEY,
            placa VARCHAR(10) NOT NULL UNIQUE,
            num_asientos INT NOT NULL,
            dni VARCHAR(20) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (dni) REFERENCES usuarios(dni)
        )"
            );
            DB::statement(
                "CREATE TABLE IF NOT EXISTS vehiculos_solicitud (
                    dni_usuario VARCHAR(20) PRIMARY KEY,
                    num_asientos_activos INT NOT NULL DEFAULT 0,
                    estado TINYINT(1) NOT NULL DEFAULT 1,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    FOREIGN KEY (dni_usuario) REFERENCES usuarios(dni)
                );"
            );

            DB::statement(
                "CREATE TABLE IF NOT EXISTS reportes_viaje (
                     id INT AUTO_INCREMENT PRIMARY KEY,
                     dni_usuario VARCHAR(20) NOT NULL,
                     dni_conductor VARCHAR(20) NOT NULL,
                     num_asientos INT NOT NULL DEFAULT 0,
                     lng_inicial DOUBLE NOT NULL,
                     lat_inicial DOUBLE NOT NULL,
                     lng_final DOUBLE NOT NULL,
                     lat_final DOUBLE NOT NULL,
                     lng_final_real DOUBLE,
                     lat_final_real DOUBLE,
                     direccion_final TEXT,
                     estado TINYINT(1) NOT NULL DEFAULT 1,  -- 1 = activo, 0 = inactivo
                     valoracion INT NOT NULL DEFAULT 0,
                     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                     updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        FOREIGN KEY (dni_usuario) REFERENCES usuarios(dni),
                        FOREIGN KEY (dni_conductor) REFERENCES usuarios(dni)
                     );"
            );
        } catch (\Throwable $e) {
            return ConfigModel::withJsonResponse(400, $e->getMessage());
        }
    }
}
