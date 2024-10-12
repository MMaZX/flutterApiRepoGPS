<?php

namespace App\Http\Controllers;

use App\Models\Usuarios;
use App\Models\Usuarioss;
use ConfigModel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\throwException;

class UsuariosController extends Controller
{
    public function __construct()
    {
        Usuarios::crearTabla();
    }


    public function addUser(Request $request)
    {
        try {
            $data  = $request->json()->all();
            $nombre = $data['nombre'];
            $apellidoPaterno = $data['apellido_paterno'];
            $apellidoMaterno = $data['apellido_materno'];
            $dni = $data['dni'];
            $celular = $data['celular'];
            $usuario = $data['usuario'];
            $clave = $data['clave'];
            $rol = $data['rol'];
            if ($rol != 'PASAJERO' && $rol != 'CONDUCTOR' && $rol != 'ADMIN') {
                return ConfigModel::exception(400, 'Rol inválido');
            }
            if (Usuarios::where('usuario', $usuario)->exists()) {
                return ConfigModel::exception(400, 'El usuario ya existe');
            }

            if (Usuarios::where('dni', $dni)->exists()) {
                return ConfigModel::exception(400, 'El DNI ya existe');
            }
            if (Usuarios::where('celular', $celular)->exists()) {
                return ConfigModel::exception(400, 'El celular ya existe');
            }

            if (empty($apellidoMaterno) || empty($apellidoPaterno) || empty($nombre) || empty($dni)  || empty($celular) || empty($rol)) {
                return ConfigModel::exception(400, 'Debe completar todos los campos para registrar el usuario correctamente');
            }

            if ($usuario == null || $clave == null) {
                return ConfigModel::exception(400, 'Usuario y clave son requeridos');
            }

            Usuarios::create([
                'nombre' => $nombre,
                'apellido_paterno' => $apellidoPaterno,
                'apellido_materno' => $apellidoMaterno,
                'dni' => $dni,
                'celular' => $celular,
                'usuario' => $usuario,
                'clave' => $clave,
                'rol' => $rol,
            ]);
            return ConfigModel::withJsonResponse(200, "Usuario creado correctamente");
        } catch (\Throwable $e) {
            return ConfigModel::withJsonResponse(400, $e->getMessage());
        }
    }

    // Listar todos los Usuarioss
    public function getUsers()
    {
        $Usuarioss = Usuarios::all();
        return response()->json($Usuarioss);
    }

    public function getUserById($id)
    {
        // $Usuarios = Usuarios::where('usuario', $id)->get()->first();
        // return response()->json($Usuarios);
        $Usuarios = "SELECT u.*, v.placa, v.num_asientos FROM usuarios u LEFT JOIN vehiculos v ON u.dni = v.dni WHERE u.usuario =  ? ";
        $result = DB::select($Usuarios, [$id])[0    ];
        return response()->json($result);
    }


    public function getUserByDNI($dni)
    {
        $Usuarios = Usuarios::where('dni', $dni)->get()->first();
        return response()->json($Usuarios);
    }


    public function getUserActivesForDNI($dni)
    {
        $Usuarios = "SELECT * FROM reportes_viaje WHERE dni_conductor = ? AND estado = 0";
        $result = DB::select($Usuarios, [$dni]);
        return response()->json($result);
    }

    // Eliminar un Usuarios
    public function deleteUsers($id)
    {
        $Usuarios = Usuarios::findOrFail($id);
        $Usuarios->delete();
        return ConfigModel::withJsonResponse(204, 'Se eliminó el usuario correctamente');
    }

    public function login(Request $request)
    {
        try {
            $data  = $request->json()->all();
            $user = $data['usuario'];
            $clave = $data['clave'];

            if (!isset($user) && !isset($clave)) {
                return ConfigModel::exception(400, 'Usuario y clave son requeridos');
            }

            $user = Usuarios::where([
                'usuario' => $user,
                'clave' => $clave
            ])->first();

            if (empty($user)) {
                return ConfigModel::exception(401, 'Credenciales inválidas');
            }
            return ConfigModel::withJsonResponse(200, 'Sesión exitoso');
        } catch (\Throwable $e) {
            return ConfigModel::withJsonResponse(400, $e->getMessage());
        }
    }
}
