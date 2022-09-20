<?php


namespace App\Controllers;

use App\Models\SGVehiculos; 
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;

class SGVehiculoController
{
	protected $customResponse;

	protected $validator;

	protected $vehiculo;

	public function __construct()
	{
		$this->customResponse = new CustomResponse();

		$this->validator = new Validator();

		$this->vehiculo = new SGVehiculos();
	}

	/*
	*ENPOINT POST save vehiculos
	*/
	public function save(Request $request , Response $response)
	{
		$this->validator->validate($request , [
			"vehiculo_nombre_tarjeta" => v::notEmpty(),
			"id_marca"	 			=> v::notEmpty(),
			"vehiculo_color" => v::notEmpty(),
			"vehiculo_placa" => v::notEmpty(),
			"vehiculo_cilindraje" => v::notEmpty(),
			"id_usuario" => v::notEmpty(),
			"id_empresa" => v::notEmpty(),	
			"vehiculo_tipo" => v::notEmpty()

		]);

		if($this->validator->failed())
		{
			$responseMessage = $this->validator->errors;

			return	$this->customResponse->is400Response($response , $responseMessage);


		}

		if($this->placaExist(CustomRequestHandler::getParam($request , "vehiculo_placa")))
		{
			$responseMessage = "la placa ya existe";

			return $this->customResponse->is400Response($response , $responseMessage);
		}

		$this->vehiculo->create([
			"vehiculo_nombre_tarjeta" => CustomRequestHandler::getParam($request , "vehiculo_nombre_tarjeta"),
			"id_marca"	 			=> CustomRequestHandler::getParam($request , "id_marca"),
			"vehiculo_color" => CustomRequestHandler::getParam($request , "vehiculo_color"),
			"vehiculo_placa" => CustomRequestHandler::getParam($request , "vehiculo_placa"),
			"vehiculo_cilindraje" => CustomRequestHandler::getParam($request , "vehiculo_cilindraje"),
			"vehiculo_modelo" => CustomRequestHandler::getParam($request , "vehiculo_modelo"),
			"id_usuario" => CustomRequestHandler::getParam($request , "id_usuario"),
			"vehiculo_imagen" => CustomRequestHandler::getParam($request , "vehiculo_imagen"),
			"id_empresa" => CustomRequestHandler::getParam($request , "id_empresa"),
			"vehiculo_tipo" => CustomRequestHandler::getParam($request , "vehiculo_tipo"),
			"fecha" => date("Y-m-d")
		]);

		$responseMessage = "creado";

		$this->customResponse->is200Response($response , $responseMessage);
	}

	/*
	*ENDPOINT GET list id_usuario 
	*/
	public function listFindByIdUsuario(Request $request , Response $response , $id)
	{
		$getVehiculoByUser = $this->vehiculo->where(["id_usuario" => $id])->get();

		return $this->customResponse->is200Response($response , $getVehiculoByUser);
	}

	/*
	*ENDPOINT GET id_vehiculo
	*/
	public function findById(Request  $request  , Response $response , $id)
	{
		$getFindById = $this->vehiculo->where(["vehiculo_id" => $id])->get();

		$this->customResponse->is200Response($response , $getFindById);
	}

	public function placaExist($placa)
	{
		$getPlaca = $this->vehiculo->where("vehiculo_placa" , "=" , $placa)->count();

		if($getPlaca > 0)
		{
			return true;
		
		}else{

			return false;
		}
	}

	/*
	*ENDPOINT POST BUSCAR X EMPRESA
	*/
	public function listFindByEmpresa(Request $request  , Response $response , $id)
	{
		$getFindByEmpresa = $this->vehiculo->selectRaw("han_sg_vehiculos.vehiculo_id , 
												han_sg_vehiculos.vehiculo_nombre_tarjeta,
												han_sg_vehiculos.vehiculo_color,
												han_sg_vehiculos.vehiculo_placa,
												han_sg_vehiculos.vehiculo_tipo,
												han_marca.marca_nombre,
												users.user
												")
											->join("han_marca" , "han_marca.id_marca" , "=" , "han_sg_vehiculos.id_marca")
											->join("users" , "users.id" , "=" , "han_sg_vehiculos.id_usuario")
											->where(["han_sg_vehiculos.id_empresa" => $id])->get();

		$this->customResponse->is200Response($response , $getFindByEmpresa);
	}

	/**
	 * ENDPOINT DELETE ELIMINAR POR ID*/
	public function deleteById(Request $request , Response $response , $id)
	{

		$this->vehiculo->where(["vehiculo_id" => $id])->delete();

		$responseMessage = "Eliminado";

		$this->customResponse->is200Response($response , $responseMessage);
	}

	/**
	 * ENDPOINT PATCH FOR ID*/
	public function updated(Request $request , Response $response , $id)
	{
		$this->validator->validate($request , [
			"vehiculo_nombre_tarjeta" => CustomRequestHandler::getParam($request , "vehiculo_nombre_tarjeta"),
			"id_marca"	 			=> CustomRequestHandler::getParam($request , "id_marca"),
			"vehiculo_color" => CustomRequestHandler::getParam($request , "vehiculo_color"),
			"vehiculo_placa" => CustomRequestHandler::getParam($request , "vehiculo_placa"),
			"vehiculo_cilindraje" => CustomRequestHandler::getParam($request , "vehiculo_cilindraje"),
			"vehiculo_modelo" => CustomRequestHandler::getParam($request , "vehiculo_modelo"),
			"id_usuario" => CustomRequestHandler::getParam($request , "id_usuario"),
			"vehiculo_imagen" => CustomRequestHandler::getParam($request , "vehiculo_imagen"),
			"id_empresa" => CustomRequestHandler::getParam($request , "id_empresa"),
			"vehiculo_tipo" => CustomRequestHandler::getParam($request , "vehiculo_tipo"),
		]);
		if ($this->validator->failed()) {
			
			$responseMessage = $this->validator->errors;

			return $this->customResponse->is400Response($response , $responseMessage);
		}

		if($this->placaExist(CustomRequestHandler::getParam($request , "vehiculo_placa")))
		{
			$responseMessage = "la placa ya existe";

			return $this->customResponse->is400Response($response , $responseMessage);
		}


		$this->vehiculo->where(["vehiculo_id" => $id])->update([
			"vehiculo_nombre_tarjeta" => CustomRequestHandler::getParam($request , "vehiculo_nombre_tarjeta"),
			"id_marca"	 			=> CustomRequestHandler::getParam($request , "id_marca"),
			"vehiculo_color" => CustomRequestHandler::getParam($request , "vehiculo_color"),
			"vehiculo_placa" => CustomRequestHandler::getParam($request , "vehiculo_placa"),
			"vehiculo_cilindraje" => CustomRequestHandler::getParam($request , "vehiculo_cilindraje"),
			"vehiculo_modelo" => CustomRequestHandler::getParam($request , "vehiculo_modelo"),
			"id_usuario" => CustomRequestHandler::getParam($request , "id_usuario"),
			"vehiculo_imagen" => CustomRequestHandler::getParam($request , "vehiculo_imagen"),
			"id_empresa" => CustomRequestHandler::getParam($request , "id_empresa"),
			"vehiculo_tipo" => CustomRequestHandler::getParam($request , "vehiculo_tipo")
		]);

		$responseMessage = "Actualizado";

		$this->customResponse->is200Response($response , $responseMessage);
	}

}

?>