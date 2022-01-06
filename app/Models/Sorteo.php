<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Sorteo extends Model
{

    protected  $table = "sorteo";
 
    protected $fillable =[
        "id",
        "nombre",
        "apellido",
        "contrato",
        "municipio",
        "documento",
        "ganador",
        "id_servicio",
        "created_at",
        "updated_at"
    ];

}