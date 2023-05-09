<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class TicketDetalle extends Model
{
 
    protected  $table = "ticket_detalle";
 
    protected $fillable =[
        "id",
        "comentario",
        "id_user",
        "fecha",
        "created_at",
        "updated_at",
        "id_ticket"
    ];

}

?>