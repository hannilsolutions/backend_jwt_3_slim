<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Han_Views extends Model
{

    protected  $table = "han_views";
 
    protected $fillable =[
    "id",
    "titile",
    "url",
    "created_at",
    "updated_at"
];

}

?>