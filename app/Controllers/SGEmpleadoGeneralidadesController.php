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

		if($this->validator->failed())
		{
			$responseMessage = $this->validator->errors;

			return $this->customResponse->is400Response($response , $responseMessage);
		}
		$empleado = CustomRequestHandler::getParam($request , "empleado_id");

		$permiso = CustomRequestHandler::getParam($request  , "permiso_id");

		$getGeneralidadesEmpresa = $this->getListGeneralidades(CustomRequestHandler::getParam($request , "id_empresa") , CustomRequestHandler::getParam($request , "tipo"));

		#create el tipo
		foreach($getGeneralidadesEmpresa as $item)
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

	/*
	*ENDPOINT POST 
	*/
	public function findByEmpleadoAndPermisoAndTipo(Request $request , Response $response)
	{
		$this->validator->validate($request , [
			"empleado_id" => v::notEmpty(),
			"permiso_id"	=> v::notEmpty(),
			"tipo" => v::notEmpty(),
		]);

		if($this->validator->failed())
		{
			$responseMessage = $this->validator->failed();

			return $this->customResponse->is400Response($response , $responseMessage);
		}

		$getListGeneralidadesEmpleado = $this->sgEmpleadoGeneralidades->selectRaw("
							han_sg_empleados_generalidades.empleado_generalidades_id ,
							han_sg_empleados_generalidades.active, 
							han_sg_generalidades.nombre")
		->join("han_sg_generalidades" , "han_sg_generalidades.id_generalidades" , "=" , "han_sg_empleados_generalidades.generalidades_id")
		->where(["han_sg_empleados_generalidades.empleado_id" => CustomRequestHandler::getParam($request , "empleado_id")])
		->where(["han_sg_empleados_generalidades.permiso_id" => CustomRequestHandler::getParam($request , "permiso_id")])
		->where(["han_sg_generalidades.tipo" => CustomRequestHandler::getParam($request , "tipo")])
		->get();

		$this->customResponse->is200Response($response , $getListGeneralidadesEmpleado);
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