<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class InventarioBodegas extends Model
{

     public $timestamps = false;

    protected  $table = "han_inventario_bodegas";
 
    protected $fillable =[
    "bodega_id",
    "bodega_nombre" 
];

}

?>