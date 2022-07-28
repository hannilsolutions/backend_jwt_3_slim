<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class SGTipoTrabajo extends  Model
{

    protected $table="han_sg_tipos_trabajo";
    
    protected $fillable = 
    [
    "id_tipo",
    "nombre",
    "id_empresa",
    "created_at",
    "updated_at"];
    
}

?>