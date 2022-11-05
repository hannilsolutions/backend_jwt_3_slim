<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class User extends Model
{

     protected  $table = "users";
 
    protected $fillable =[
    "id",
    "user",
    "password",
    "marca",
    "active" , 
    "token_pw",
    "fecha_caducidad",
    "email",
    "url_img",
    "role", 
    "firma",
    "id_empresa",
    "private_key",
    "public_key",
    "created_at",
    "updated_at"
];
}

?>