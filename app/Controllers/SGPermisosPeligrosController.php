<?php

namespace App\Controllers;

use App\Models\SGPermisosPeligros; 
use App\Models\SGControles;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class SGPermisosPeligrosController
{

	protected $validator;

	protected $sgPermisosPeligros;

	protected $customResponse;

	protected $sgControles;

	public function __construct()
	{
		$this->validator = new Validator();

		$this->sgPermisosPeligros = new SGPermisosPeligros();

		$this->customResponse = new CustomResponse();

		$this->sgControles = new SGControles();

	}

	/**
	 * ENDPOINT POST guarda los peligros del permiso de trabajo
	 * */

	public function save(Request $request , Response $response)
	{
		$this->validator->validate($request , [

			"usuario_id" => v::notEmpty(),
			"permiso_id" => v::notEmpty(),
			"peligro_id" => v::notEmpty(),
		]);

		if ($this->validator->failed()) {
			
			$responseMessage = $this->validator->errors;

			return $this->customResponse->is400Response($response , $responseMessage);
		}

		$getVerifyExist = $this->verifyExist(CustomRequestHandler::getParam($request , "permiso_id") , CustomRequestHandler::getParam($request , "peligro_id"));
		
		if ($getVerifyExist) {

			$responseMessage = "ya existe el peligro";	

			return $this->customResponse->is400Response($response , $responseMessage);
		}

		$this->sgPermisosPeligros->create([
			"usuario_id" => CustomRequestHandler::getParam($request , "usuario_id"),
			"permiso_id" => CustomRequestHandler::getParam($request , "permiso_id"),
			"peligro_id" => CustomRequestHandler::getParam($request , "peligro_id")
		]);

		$responseMessage = "creado";

		$this->customResponse->is200Response($response , $responseMessage);
	}

	/**
	 * function valida si ya fue cargado el peligro en el permiso de trabajo*/
	public function verifyExist($permiso , $peligro)
	{
		$count = $this->sgPermisosPeligros
										->where("permiso_id" , "=" , $permiso)
										->where("peligro_id" , "=" , $peligro)
										->count();

		if ($count > 0) {
			
			return true;

		}else{

			return false;
		}
	}

	/**
	 * ENDPOINT DELETE eliminar */
	public function delete(Request $request , Response $response , $id)
	{
		$this->sgPermisosPeligros->where(["permiso_peligro_id" => $id])->delete();

		$responseMessage = "eliminado";

		$this->customResponse->is200Response($response , $responseMessage);
	}

	/**
	 * ENDPOINT GET listar por permiso*/
	public function listByPermiso(Request $request , Response  $response , $id)
	{
		$getListByPermiso = $this->sgPermisosPeligros->selectRaw(
								"han_sg_permisos_peligros.permiso_peligro_id,
								han_sg_permisos_peligros.peligro_id,
								peligros.nombre as peligro_nombre,
								clasificacion.nombre as clasificacion_nombre"
								)->join("han_sg_peligros as peligros" , 		"peligros.id_peligro" , "=" , "han_sg_permisos_peligros.peligro_id" )
								->join("han_sg_clasificacion as clasificacion" , "clasificacion.id_clasificacion", "=", "peligros.id_clasificacion")
								->where(["han_sg_permisos_peligros.permiso_id" => $id])->get();
		$responseMessage = array();

		foreach($getListByPermiso as $item)
		{
			$temp = [
				"peligro_nombre" => $item->peligro_nombre,
				"permiso_peligro_id" => $item->permiso_peligro_id,
				"clasificacion_nombre" => $item->clasificacion_nombre,
				"controles" => $this->controles($item->peligro_id)
			];

			array_push($responseMessage , $temp);
		}	

		$this->customResponse->is200Response($response , $responseMessage);
	}
	/**
	 * 
	 * */
	public function controles($peligro_id)
	{	
		$getlistControles = $this->sgControles->where("id_peligro" , "=" , $peligro_id)->get();

		return $getlistControles;
	}


}

?>