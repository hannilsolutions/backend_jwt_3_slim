<?php

namespace App\Controllers;

use App\Models\SGPermisoVehiculo;
use App\Models\SGGeneralidades;  
use App\Models\SGVehiculosGeneralidades;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class SGPermisosVehiculoController
{
	protected $validator;

	protected $permisoVehiculo;

	protected $customResponse;

	protected $generalidades;

	protected $vehiculoGeneralidades;

	public function __construct()
	{
		$this->validator = new Validator();

		$this->permisoVehiculo = new SGPermisoVehiculo();

		$this->customResponse = new CustomResponse();

		$this->generalidades = new SGGeneralidades();

		$this->vehiculoGeneralidades = new SGVehiculosGeneralidades();
	}

	/**
	 * ENDPOINT save*/

	public function save(Request $request , Response $response)
	{
		$this->validator->validate($request , [
			"permiso_id" => v::notEmpty(),
			"vehiculo_id" => v::notEmpty(),
			"tipo" => v::notEmpty(),
			"id_empresa" => v::notEmpty(),
		]);

		if ($this->validator->failed()) {
			
			$responseMessage = $this->validator->errors;

			return $this->customResponse->is400Response($response , $responseMessage);
		}

		//create
		try{
			
			
		 
		 $generalidades = $this->getGeneralidades(CustomRequestHandler::getParam($request, "tipo") , CustomRequestHandler::getParam($request , "id_empresa"));

		 $count = count($generalidades);
 
		 if ($count == 0) {
		 	
		 	$responseMessage = "Sin generalidades";

		 	return $this->customResponse->is400Response($response , $responseMessage);
		 }

		 $insert = $this->permisoVehiculo->create([
			"permiso_id" => CustomRequestHandler::getParam($request , "permiso_id"),
			"vehiculo_id" => CustomRequestHandler::getParam($request , "vehiculo_id")
		]);

		$id = $insert->id;

		 foreach($generalidades as $item)
		 {
		 	$this->vehiculoGeneralidades->create([
		 		"permiso_vehiculo_id" => $id,
		 		"generalidades_id" => $item->id_generalidades
		 	]);
		 }

		 $responseMessage = "creado";

		$this->customResponse->is200Response($response , $responseMessage);

		}catch(Exception $e)
		{
			$responseMessage = $e->getMessage();

			return $this->customResponse->is400Response($response , $responseMessage);
		}


		 


	}

	public function getGeneralidades($tipo , $idempresa)
	{
		return $this->generalidades->where("id_empresa" , "=" , $idempresa)->where("tipo" , "=" , $tipo)->get();

	}

	/**
	 * ENDPOINT GET findByIdEmpresa*/

	public function findByPermiso(Request $request , Response $response , $id)
	{
		try{

			$getinfo = $this->permisoVehiculo->join("han_sg_vehiculos" , "han_sg_vehiculos.vehiculo_id" , "=" ,"han_sg_permisos_vehiculos.vehiculo_id")->where(["permiso_id" => $id])->get();

			$this->customResponse->is200Response($response , $getinfo);

		}catch(Exception $e)
		{
			return $this->customResponse->is400Response($response , $e->getMessage());
		}
	}

}
