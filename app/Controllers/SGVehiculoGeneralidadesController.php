<?php

namespace App\Controllers;

use App\Models\SGVehiculosGeneralidades;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



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

}

?>














