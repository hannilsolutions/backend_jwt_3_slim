<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Han_Relations extends Model
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