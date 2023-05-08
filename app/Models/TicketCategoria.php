<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TicketCategoria extends Model
{

    public $timestamps = false;

    protected  $table = "ticket_categoria";
 
    protected $fillable =[
        "id",
        "name"
    ];

}

?>