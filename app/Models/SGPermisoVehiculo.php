<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class SGPermisoVehiculo extends  Model
{
    public $timestamps = false;
    

    protected $table="han_sg_permisos_vehiculos";
    
    protected $fillable = 
    [
    "permiso_vehiculo_id",
    "permiso_id",
    "vehiculo_id"
    ];
    
}

?>