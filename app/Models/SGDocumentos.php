<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class SGDocumentos extends  Model
{

    protected $table="han_sg_documentos";
    
    protected $fillable = 
    [
    "documento_id",
    "documento_nombre",
    "documento_url",
    "documento_caducidad",
	"documento_inicio",
	"documento_fin",
    "documento_tipo",
    "referencia_id",
    "created_at" , 
    "updated_at",
    "documento_estado"];
    
}

?>