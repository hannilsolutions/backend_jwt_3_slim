<?php

namespace App\Controllers;

use App\Models\SGVehiculosGeneralidades;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;
use Illuminate\Database\QueryException;


class SGVehiculoGeneralidadesController
{
	protected $customResponse;

	protected $vehiculoGeneralidades;

	protected $validator;

	public function __construct()
	{
		$this->customResponse = new CustomResponse();

		$this->vehiculoGeneralidades = new SGVehiculosGeneralidades();

		$this->validator = new Validator();
	}
	/**
	 * ENDPOINT GET disct de item generalidades vehiculo*/

	public function disctGeneralidades(Request $request , Response $response , $id)
	{
		/*SELECT distinct(han_sg_generalidades.item) AS item FROM internet_pagos.han_sg_vehiculos_generalidades 
			inner join han_sg_generalidades on han_sg_generalidades.id_generalidades = han_sg_vehiculos_generalidades.generalidades_id
			where han_sg_vehiculos_generalidades.permiso_vehiculo_id = 5 */
		$getDisctItem = $this->vehiculoGeneralidades->selectRaw("DISTINCT han_sg_generalidades.item")->join("han_sg_generalidades" , "han_sg_generalidades.id_generalidades", "=", "han_sg_vehiculos_generalidades.generalidades_id")->where(["han_sg_vehiculos_generalidades.permiso_vehiculo_id" => $id])->get();

		$this->customResponse->is200Response($response , $getDisctItem);
	}

	public function editDisctTrailer(Request $request , Response $response , $id){
		/**
		 * select hsvg.vehiculo_generalidades_id  FROM han_sg_vehiculos_generalidades hsvg inner join han_sg_generalidades hsg on hsg.id_generalidades = hsvg.generalidades_id 
         * WHERE hsg.item = "Inspección de trailer" and hsvg.permiso_vehiculo_id = 690
		 */
		$getDisct = $this->vehiculoGeneralidades->selectRaw("han_sg_vehiculos_generalidades.vehiculo_generalidades_id")
							->join("han_sg_generalidades" , "han_sg_generalidades.id_generalidades", "=", "han_sg_vehiculos_generalidades.generalidades_id")
							->where(["han_sg_vehiculos_generalidades.permiso_vehiculo_id" => $id])
							->where(["han_sg_generalidades.item"=>CustomRequestHandler::getParam($request , "item")])->get();
		foreach($getDisct as $item)
		{
			$this->vehiculoGeneralidades->where(["vehiculo_generalidades_id"=>$item->vehiculo_generalidades_id])->update([
				"inspeccion" => "Na"
			]);
		}

		$this->customResponse->is200Response($response , "Actualizado");
	}

	/**
	 * ENDPOINT POST findByNameGeneralidades de los vehiculos
	 * 
	 * SELECT han_sg_vehiculos_generalidades.vehiculo_generalidades_id, 
    han_sg_vehiculos_generalidades.inspeccion,
    han_sg_generalidades.nombre
    
	FROM internet_pagos.han_sg_vehiculos_generalidades
inner join han_sg_generalidades on han_sg_generalidades.id_generalidades = han_sg_vehiculos_generalidades.generalidades_id

where han_sg_generalidades.item = 'Otros'*/
	public function findByNameGeneralidadesVehiculos(Request $request , Response $response)
	{
		$this->validator->validate($request , [

			"item" => v::notEmpty(),
			"id_vehiculo" => v::notEmpty()
		]);

		if ($this->validator->failed()) {
			
			$responseMessage = $this->validator->errors;

			return $this->customResponse->is400Response($response , $responseMessage);
		}

		try
		{
			$item = CustomRequestHandler::getParam($request , "item");

			$responseMessage = $this->vehiculoGeneralidades->selectRaw("han_sg_vehiculos_generalidades.vehiculo_generalidades_id, 
    												han_sg_vehiculos_generalidades.inspeccion,
    												han_sg_generalidades.nombre")
													->join("han_sg_generalidades" , "han_sg_generalidades.id_generalidades" ,"=" ,"han_sg_vehiculos_generalidades.generalidades_id")
													->where(["han_sg_generalidades.item" => CustomRequestHandler::getParam($request , "item")])
													->where(["han_sg_vehiculos_generalidades.permiso_vehiculo_id" => CustomRequestHandler::getParam($request , "id_vehiculo")])
													->get();
			$this->customResponse->is200Response($response , $responseMessage);

		}catch(QueryException $e)
		{
			return $this->customResponse->is400Response($response , $e );
		}
	}

	/**
	 * ENDPOINT PATCH EDIT INSPECCION*/
	public function editInspeccion(Request $request , Response $response , $id)
	{
		$this->validator->validate($request , [ 
    	"inspeccion" => v::notEmpty()
		]);

		if ($this->validator->failed()) {
			
			$responseMessage = $this->validator->errors;

			return $this->customResponse->is400Response($response , $responseMessage);
		}
		 

		try{

			$this->vehiculoGeneralidades->where(["vehiculo_generalidades_id"=>$id])->update([
				"inspeccion" => CustomRequestHandler::getParam($request , "inspeccion")
			]);

			$responseMessage = "actualizado";

			$this->customResponse->is200Response($response , $responseMessage);


		}catch(QueryException $e){

			return $this->customResponse->is400Response($response , $e);
		}
	}

	 
}

?>














