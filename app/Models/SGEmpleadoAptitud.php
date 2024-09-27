<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class SGEmpleadoAptitud extends Model
{

    public $timestamps = false;

    protected  $table = "han_sg_empleado_aptitud";
 
    protected $fillable =[
     "id_empleado_aptitud",
     "id_generalidades",
     "respuesta",
     "id_permiso_aptitud"
      
];

}

?>