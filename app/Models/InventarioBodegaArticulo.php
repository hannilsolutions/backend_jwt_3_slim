<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class InventarioBodegaArticulo extends Model
{
 

    protected  $table = "han_inventario_bodegas_articulos";
 
    protected $fillable =[
     "bodega_articulo_id",
     "articulo_id",
     "bodega_id",
     "cantidad",
     "created_at",
     "updated_at"
];

}

?>