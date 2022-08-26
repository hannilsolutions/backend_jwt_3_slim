<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class SGVehiculos extends  Model
{

    protected $table="han_sg_vehiculos";
    
    protected $fillable = 
    ["id_vehiculo",
    "vehiculo_nombre_tarjeta",
    "id_marca",
    "vehiculo_color",
    "vehiculo_placa",
    "vehiculo_cilindraje" ,
    "vehiculo_modelo",
    "id_usuario",
    "vehiculo_imagen",
    "created_at",
    "fecha",
    "updated_at"
 ];
    
}

?>