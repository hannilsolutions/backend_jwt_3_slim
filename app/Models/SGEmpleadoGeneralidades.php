<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class SGEmpleadoGeneralidades extends  Model
{

    protected $table="han_sg_empleados_generalidades";
    
    protected $fillable = 
    ["empleado_generalidades_id",
    "empleado_id",
    "permiso_id" , 
    "generalidades_id" ,
     "created_at",
     "active",
    "updated_at"];
    
}

?>