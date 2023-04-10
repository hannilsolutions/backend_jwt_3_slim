<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class SGFirma extends Model
{

    public $timestamps = false;

    protected  $table = "han_sg_firmas";
 
    protected $fillable =[
     "id",
     "id_empresa",
     "id_user",
     "cargo", 
     "estado"
      
];

}

?>