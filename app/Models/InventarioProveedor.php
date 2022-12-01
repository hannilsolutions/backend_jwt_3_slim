<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class InventarioProveedor extends Model
{

    public $timestamps = false;

    protected  $table = "han_inventario_proveedor";
 
    protected $fillable =[
     "proveedor_id",
     "proveedor_name",
     "proveedor_nit",
     "proveedor_celular",
      
];

}

?>