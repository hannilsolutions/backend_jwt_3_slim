<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Marcas extends Model
{

    protected  $table = "han_marca";
 
    protected $fillable =[
    "id_marca",
    "marca_nombre", 
    "created_at",
    "updated_at"
];

}

?>