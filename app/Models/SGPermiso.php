<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class SGPermiso extends  Model
{

    protected $table="han_sg_permiso_trabajo";
    
    protected $fillable = 
    [
    "id_permiso",
    "fecha_inicio",
    "hora_inicio",
    "fecha_cierre",
    "hora_cierre",
	"lugar_de_trabajo",
	"created_at",
    "updated_at"];
    
}

?>