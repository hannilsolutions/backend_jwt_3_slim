<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class InventarioIngresoDetalle extends Model
{

    public $timestamps = false;

    protected  $table = "han_inventario_ingresos_detalles";
 
    protected $fillable =[
     "ingreso_detalle_id",
     "articulo_id",
     "ingreso_id",
     "ingreso_detalle_cantidad",
     "ingreso_detalle_compra",
     "ingreso_detalle_venta"
];

}

?>