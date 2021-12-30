<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{

    protected  $table = "pagos_supergiros";
 
    protected $fillable =["fecha_recaudo",
    "hora_recaudo","id_transaccion","cc_asesor" , 
    "nombre_asesor", "codigo_pto_vta","nombre_pto_vta",
    "nombre_convenio","numero_referencia","valor_total",
    "cc_cliente","nombre_cliente","estado_descargue","reversado",
    "comentario_reversado" , "fecha_reversado","created_at","updated_at"];

}