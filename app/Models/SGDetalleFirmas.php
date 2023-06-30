<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class SGDetalleFirmas extends  Model
{

    protected $table="han_sg_detalle_firma";
    
    protected $fillable = 
    [
    "id",
    "id_firma",
    "url_firma",
	"created_at",
	"updated_at",
    "fecha_firma",
    "id_permiso", 
    "contenido"];
    
}

?>