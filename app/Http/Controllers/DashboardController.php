<?php

namespace App\Http\Controllers;

use App\Models\Dashboard;
use App\Models\Incidencias;
use App\Models\Usuarios;
use App\Models\Vehiculos;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        Vehiculos::crearTabla();
        Usuarios::crearTabla();
        Incidencias::crearTabla();
    }


    public function  getDashboard()
    {
        $dash = Dashboard::getData();
        return $dash;
    }
}
