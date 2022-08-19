<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class SGEmpresa extends  Model
{

    protected $table="han_sg_empresa";
    
    protected $fillable = 
    [
    "id_empresa",
    "razon_social",
    "nit",
    "dv",
    "logo",
	"codigo_soporte",
    "direccion",
    "prefijo",
    "html1",
    "html2",
    "html3",
	"created_at",
    "updated_at"];
    
}

?>