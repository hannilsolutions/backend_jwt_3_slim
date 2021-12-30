<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class User extends Model
{

    protected  $table = "users";

    #protected $fillable =["user","email","password", "active" , "token_pw" , "fecha_caducidad" , "created_at" , "updated_at"];
    protected $fillable =["user","email","password"];

}