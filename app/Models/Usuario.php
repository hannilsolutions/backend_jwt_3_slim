<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Usuario extends Model
{

    protected  $table = "users";
 
    protected $fillable =[
    "id",
    "user",
    "marca",
    "active" , 
    "email",
    "url_img",
    "role", 
    "created_at",
    "updated_at"
];

}