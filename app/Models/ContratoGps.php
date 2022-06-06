<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class ContratoGps extends  Model
{

    protected $table="han_contrato_gps";
    
    protected $fillable = 
    ["id_contrato",
    "latitud",
    "longitud" , 
    "created_at" ,
     "updated_at"];
    
}