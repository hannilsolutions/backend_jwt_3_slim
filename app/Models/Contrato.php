<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class Contrato extends  Model
{

    protected $table="contratos";
    
    protected $fillable = 
    ["id_contrato",
    "preferencia_factura",
    "observacion" , 
    "created_at" ,
     "updated_at"];
    
}