<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Han_Uploads extends Model
{

    protected  $table = "han_uploads";
 
    protected $fillable =[
    "id",
    "titulo",
    "categoria",
    "tipo",
    "fecha",
    "created_at",
    "updated_at"
];

}

?>