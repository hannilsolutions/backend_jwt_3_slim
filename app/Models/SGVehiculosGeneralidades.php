<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class SGVehiculosGeneralidades extends  Model
{
     
    

    protected $table="han_sg_vehiculos_generalidades";
    
    protected $fillable = 
    [
    "vehiculo_generalidades_id",
    "permiso_vehiculo_id",
    "generalidades_id",
    "active",
    "inspeccion",
    "created_at",
    "updated_at"
    ];
    
}

?>