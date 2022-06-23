<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class SGControles extends  Model
{

    protected $table="han_sg_controles";
    
    protected $fillable = 
    [
    "id_control",
    "nombre",
    "id_peligro",
	"created_at",
	"updated_at"];
    
}

?>