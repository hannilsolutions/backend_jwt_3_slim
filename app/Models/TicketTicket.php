<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TicketTicket extends Model
{
 
    protected  $table = "ticket_ticket";
 
    protected $fillable =[
        "id",
        "titulo",
        "id_categoria",
        "id_user",
        "fecha",
        "comentario",
        "estado",
        "created_at",
        "updated_at"
    ];

}

?>