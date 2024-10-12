<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class SGPermisoAptitud extends Model
{

    public $timestamps = false;

    protected  $table = "han_sg_permiso_aptitud";
 
    protected $fillable = [
     "id_permiso_aptitud",
     "id_permiso",
     "id_user",
     "estado",
     "json"
      
];

}

?>