<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class SGObservaciones extends Model
{

    protected  $table = "han_sg_permisos_observaciones";
 
    protected $fillable =[
    "id_observacion",
    "observacion",
    "id_usuario",
    "created_at",
    "updated_at" , 
    "url_img", 
    "id_permiso"];

}

?>