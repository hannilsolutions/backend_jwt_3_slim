<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class SGClasificacion extends  Model
{

    protected $table="han_sg_clasificacion";
    
    protected $fillable = 
    [
    "nombre",
	"created_at",
	"updated_at"];
    
}