<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class SGConfiguration extends Model
{

    public $timestamps = false;

    protected  $table = "configuration";
 
    protected $fillable =[
     "id",
     "code",
     "value"
      
];

}

?>