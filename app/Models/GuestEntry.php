<?php

namespace App\Models;

use \Illuminate\Database\Eloquent\Model;

class GuestEntry extends  Model
{

    protected $table="pagos_supergiros";
    
    protected $fillable = ["fecha_recaudo","hora_recaudo","id_transaccion" , "cc_asesor" , "nombre_asesor" , "nombre_pto_vta" , "numero_referencia" , "valor" , "cc_cliente" , "nombre_cliente"];
    
}