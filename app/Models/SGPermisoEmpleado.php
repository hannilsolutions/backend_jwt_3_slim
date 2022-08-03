<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class SGPermisoEmpleado extends  Model
{

    protected $table="han_sg_permisos_empleados";
    
    protected $fillable = 
    ["id_permisos_empleado",
    "id_permiso_trabajo",
    "id_user",
    "firma",
    "id_empresa",
    "created_at" ,
     "updated_at"];
    
}

?>