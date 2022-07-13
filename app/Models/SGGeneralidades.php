<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class SGGeneralidades extends Model
{

    protected  $table = "han_sg_generalidades";
 
    protected $fillable =[
    "id_generalidades",
    "nombre",
    "tipo",
    "id_empresa" , 
    "created_at", 
    "updated_at"];

}

?>