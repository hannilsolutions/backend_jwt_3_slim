<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class InventarioTransferenciaBodegas extends Model
{
 

    protected  $table = "han_inventario_transferencia_bodegas";
 
    protected $fillable =[
     "transferencia_id",
     "origen_bodega_id",
     "cantidad",
     "articulo_id",
     "destino_bodega_id",
     "fecha",
     "created_at",
     "updated_at"
];

}

?>