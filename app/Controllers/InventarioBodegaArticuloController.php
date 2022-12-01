
<?php

namespace App\Controllers;

use App\Models\InventarioBodegaArticulo;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class InventarioBodegaArticuloController
{	
	protected  $customResponse;

    protected  $bodegaArt;

    protected  $validator;

       public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->bodegaArt = new InventarioBodegaArticulo();

         $this->validator = new Validator();
    }


   //updated

    public function updatedBodegaArticulo($request)
    {

    	//validamos si existe el bodega articulo,
    	$getcount = $this->bodegaArt
    								->where("articulo_id" , "=" , CustomRequestHandler::getParam("articulo_id"))
    								->where("bodega_id" , "=" , CustomRequestHandler::getParam("bodega_id"))
    								->count();

    	if($getcount > 0)
    	{
    		//actualizamos
    		$info = $this->bodegaArt
    								->where("articulo_id" , "=" , CustomRequestHandler::getParam("articulo_id"))
    								->where("bodega_id" , "=" , CustomRequestHandler::getParam("bodega_id"))
    								->get();
    		$cantidad = 0;
    		

    		foreach($info as $item)
    		{
    			$cantidad = $cantidad + $item->cantidad;
    			$id = $item->bodega_articulo_id;
    		}

    		//updated
    		$this->bodegaArt->where("bodega_articulo_id" , "=" , $id)->update([
    			"cantidad" => $cantidad
    		]);


    	}else{
    		//creamos
    		$this->bodegaArt->create([ 
     			"articulo_id"=> CustomRequestHandler::getParam($request , "articulo_id"),
     			"bodega_id"=> CustomRequestHandler::getParam($request , "bodega_id"),
     			"cantidad"=> CustomRequestHandler::getParam($request , "cantidad"),
    		]);
    	}
    }


}

<?