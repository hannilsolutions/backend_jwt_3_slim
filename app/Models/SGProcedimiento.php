<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class SGProcedimiento extends  Model
{
     
    

    protected $table="han_sg_permiso_procedimiento";
    
    protected $fillable = 
    [
    "id_procedimiento",
    "id_user",
    "id_permiso",
    "procedimiento",   
    "created_at",
    "updated_at"
    ];
    
}

?>