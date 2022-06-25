<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class HanGruop extends Model
{

    protected  $table = "han_gruop";
 
    protected $fillable =[
    "id",
    "title",
    "icono",
    "created_at",
    "updated_at"
];

}

?>