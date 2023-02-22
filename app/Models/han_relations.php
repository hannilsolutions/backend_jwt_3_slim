<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class han_relations extends Model
{

    protected  $table = "han_relations";
 
    protected $fillable =[
    "id",
    "roles_role",
    "group_id",
    "vistas_id",
    "active",
    "created_at",
    "updated_at"
];

}

?>