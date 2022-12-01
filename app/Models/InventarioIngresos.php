<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class InventarioIngresos extends Model
{

     

    protected  $table = "han_inventario_ingresos";
 
    protected $fillable =[
    "ingreso_id",
    "ingreso_fecha",
    "ingreso_hora",
    "proveedor_id",
    "bodega_id",
    "ingreso_valor",
    "usuario_id",
    "ingreso_factura",
    "ingreso_url_scan",
    "ingreso_tipo",
    "created_at",
    "updated_at",
];

}

?>