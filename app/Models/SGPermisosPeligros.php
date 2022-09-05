<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class SGPermisosPeligros extends Model
{

    protected  $table = "han_sg_permisos_peligros";
 
    protected $fillable =[
    "permiso_peligro_id",
    "usuario_id",
    "permiso_id",
    "peligro_id",
    "created_at",
    "updated_at",
];

}

?>