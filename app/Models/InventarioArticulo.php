<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class InventarioArticulo extends Model
{

    public $timestamps = false;

    protected  $table = "han_inventario_articulos";
 
    protected $fillable =[
    "articulo_id",
    "articulo_nombre",
    "articulo_codigo",
    "articulo_valor",
    "articulo_unitario",
    "articulo_cantidad",
    "articulo_categoria"
];

}

?>