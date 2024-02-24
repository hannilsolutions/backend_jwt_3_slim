<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class DatosPersonales extends  Model
{

 	public $timestamps = false;

    protected $table="datos_personales";

   

    protected $fillable = 
    ["id",
    "id_user",
    "tipo_documento",
    "documento",
    "cargo",
    "celular"
	];
    
}

?>