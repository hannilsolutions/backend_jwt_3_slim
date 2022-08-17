<?php

namespace App\Controllers;

use App\Models\SGEmpleadoGeneralidades;
use App\Models\SGGeneralidades;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class SGEmpleadoGeneralidadesController
{

	protected $customResponse;

	protected $sgEmpleadoGeneralidades;

	protected $validator;

	protected $generalidades;

	public function __construct()
	{
		$this->customResponse = new CustomResponse();

		$this->sgEmpleadoGeneralidades = new SGEmpleadoGeneralidades();

		$this->validator = new Validator();

		$this->generalidades = new SGGeneralidades();
	}

	/*
	*ENDPOINT POST 
	*/
	public function create(Request $request , Response $response)
	{
		$this->validator->validate($request , [
			"empleado_id" => v::notEmpty(),
			"permiso_id" => v::notEmpty(),
			"tipo" => v::notEmpty(),
			"id_empresa" => v::notEmpty()
		]);

		$empleado = CustomRequestHandler::getParam($request , "empleado_id");

		$permiso = CustomRequestHandler::getParam($request  , "permiso_id");

		if($this->validator->failed())
		{
			$responseMessage = $this->validator->errors;

			$this->customResponse->is400Response($response , $responseMessage);
		}

		$generalidadesEmpresa = getListGeneralidades(CustomRequestHandler::getParam($request , "id_empresa") , CustomRequestHandler::getParam($request , "tipo"));

		#create el tipo
		foreach($getListGeneralidades as $item)
		{
			$this->sgEmpleadoGeneralidades->create([
				"empleado_id" => $empleado,
				"permiso_id" => $permiso,
				"generalidades_id" => $item->id_generalidades
			]);
		}

		$responseMessage = "creado";

		$this->customResponse->is200Response($response , $responseMessage);
		

	}

	public function getListGeneralidades($id_empresa , $tipo)
	{
		$getListGeneralidades = $this->generalidades
									->where("tipo" , "=" , $tipo)
									->where("id_empresa" , "=" , $id_empresa)
									->get();

		return $getListGeneralidades;
	}

}