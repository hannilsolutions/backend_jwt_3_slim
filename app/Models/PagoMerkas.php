<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class PagoMerkas extends Model
{

    protected  $table = "pagos_merkas";
 
    protected $fillable =[
    "id_servicio_rc",
    "rc",
    "valor",
    "celular" , 
    "log",
    "fecha",
    "created_at", 
    "updated_at"];

}

?>