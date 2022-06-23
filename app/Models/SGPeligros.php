<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class SGPeligros extends  Model
{

    protected $table="han_sg_peligros";
    
    protected $fillable = 
    [
    "id_peligro",
    "nombre",
    "consecuencias",
    "id_empresa",
    "id_clasificacion",
	"created_at",
	"updated_at"];
    
}

?>