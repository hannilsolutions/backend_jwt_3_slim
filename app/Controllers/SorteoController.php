<?php

namespace App\Controllers;

use App\Models\Sorteo;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class SorteoController

{
    protected $customResponse;

    protected $sorteo;

    protected $validator;

    public function __construct(){
        
        $this->sorteo = new Sorteo();

        $this->customResponse = new CustomResponse();

        $this->validator = new Validator();
    }

    /* request get = findByMuninipio
    */
    public function findByMunicipio(Request $request , Response $response , $id)
    {

         $ganador = $this->sorteo->Where(["id_servicio" => $id])->inRandomOrder()->first();

        $this->customResponse->is200Response($response , $ganador);
    }

    /**
     * Request patch = updateById
     * actualizar ganador 
     */
    public function updateById(Request $request , Response $response , $id)
    {
        $this->sorteo->where([ "id" => $id])->update([
            "ganador" => "1",
        ]);

        $responseMessage = "Ganador Actualizado";

         $this->customResponse->is200Response($response,$responseMessage);
    }

    /**
     * Request get = gandores
     */

     public function getGanadores(Request $request , Response $response)
     {
         $countGanadores = $this->sorteo->where(["ganador" => "1"])->count();

         if($countGanadores > 0 )
         {
             $ganadores = $this->sorteo->where(["ganador" => "1"])->get();
             return $this->customResponse->is200Response($response , $ganadores);
         }

         $responseMessage = "sin registros";

         $this->customReponse->is400Response($response , $responseMessage);
     }


}


?>