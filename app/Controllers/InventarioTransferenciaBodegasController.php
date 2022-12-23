<?php

namespace App\Controllers;

use App\Models\InventarioTransferenciaBodegas;
use App\Models\InventarioBodegaArticulo;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class InventarioTransferenciaBodegasController
{

	protected $customResponse;

	protected $transfer;

	protected $bodegaArt;

	protected $validator;

	public function __construct()
	{
		$this->customResponse = new CustomResponse();

		$this->transfer = new InventarioTransferenciaBodegas();

		$this->bodegaArt = new InventarioBodegaArticulo();

		$this->validator = new Validator();
	}

	public function save(Request $request , Response $response)
	{
		$this->validator->validate($request , [
			"origen_bodega_id" => v::notEmpty(),
			"fecha" => v::notEmpty(),
			"cantidad" => v::notEmpty(),
			"articulo_id" => v::notEmpty(),
			"destino_bodega_id" => v::notEmpty()

		]);

		if ($this->validator->failed()) {
			
			$responseMessage = $this->validator->errors;

			return $this->customResponse->is400Response($response , $responseMessage);
		}

		if ($this->validarCantidad($request)) {
			
			$responseMessage = "supera existencia";

			return $this->customResponse->is400Response($response , $responseMessage);
		}

		//guardar y disminuir de articulo_bodega

		//save
		$this->transfer->create([
			"origen_bodega_id" => CustomRequestHandler::getParam($request , "origen_bodega_id"),
			"fecha" => date("Y-m-d"),
			"cantidad" => CustomRequestHandler::getParam($request , "cantidad"),
			"articulo_id" => CustomRequestHandler::getParam($request , "articulo_id"),
			"destino_bodega_id" => customResponse::getParam($request , "destino_bodega_id"),
		]);

		$this->reducirBodega($request);

		//consultar
		$this->customResponse->is200Response($response , "cargado");

	}
/**
 * REDUCE LA CANTIDAD EN BODEGA ORIGEN, 
 * PARA AUMENTAR LA DE DESTINO*/
	public function reducirBodega($request)
	{
		$get = $this->bodegaArt->where("bodega_id" , "=" , CustomRequestHandler::getParam($request , "origen_bodega_id"))
									->where("articulo_id" , "=" , CustomRequestHandler::getParam($request , "articulo_id"))
									->get();
		$cantidad = 0;
		$id= 0;
		foreach($get as $item)
		{
			$cantidad = $item->cantidad - CustomRequestHandler::getParam($request , "cantidad");
			$id = $item->bodega_articulo_id;
		}

		//actualizar
		$this->bodegaArt->where("bodega_articulo_id" , "=" , $id)->update([
			"cantidad" => $cantidad
		]);
	}

/**
 * VALIDA LA CANTIDAD QUE EXISTE EN LAS BODEGAS
 * PARA TRANSFERENCIA, RETORNA TRUE SI LA CANTIDAD A 
 * TRANSFERIR ES MAYOR A LA CANTIDAD EXISTENTE
*/
	public function validarCantidad($request){

		//buscar info de la bodega origen
		$getOrigen = $this->bodegaArt->where("bodega_id" , "=" , CustomRequestHandler::getParam($request , "origen_bodega_id"))
									->where("articulo_id" , "=" , CustomRequestHandler::getParam($request , "articulo_id"))
									->get();
		//un solo

		$cantidad;
		foreach($getOrigen as $item)
		{
			if ($item->cantidad > CustomRequestHandler::getParam($request , "cantidad")) {
				return true;
			}
		}							

		return false;
	}

	/**
	 * ENDPOINT POST FINDBYBETWEEN*/
	public function findByBetween(Request $request , Response $response)
	{
		$this->validator->validate($request , [
			"valor1" => v::notEmpty(),
			"valor2" => v::notEmpty()
		]);

		if ($this->validator->failed()) {
			
			$responseMessage = $this->validator->errors;

			return $this->customResponse->is400Response($response , $responseMessage);
		}

		$getList = $this->transfer->selectRaw("han_inventario_transferencia_bodegas.cantidad,
												han_inventario_transferencia_bodegas.fecha,
												origen.bodega_nombre as origen,
												destino.bodega_nombre as destino,
												han_inventario_articulos.articulo_nombre")
			->join("han_inventario_bodegas as origen" ,"origen.bodega_id", "=" , "han_inventario_transferencia_bodegas.	origen_bodega_id" )
			->join("han_inventario_bodegas as destino" ,"destino.bodega_id", "=" , "han_inventario_transferencia_bodegas.destino_bodega_id")
			->join("han_inventario_articulos" , "han_inventario_articulos.articulo_id" , "=" , "han_inventario_transferencia_bodegas.articulo_id")
			->whereBetween("han_inventario_transferencia_bodegas.fecha", [CustomRequestHandler::getParam($request , "valor1") , CustomRequestHandler::getParam($request , "valor2")])->get();

		$this->customResponse->is200Response($response , $getList);
	}

	/**
	 * ENDPOINT DELETE*/
	public function delete(Request $request , Response $response , $id)
	{
		//eliminar devolver a  bodega
		$getList = $this->transfer->where("transferencia_id" => $id)->get();

		$cantidad = 0;


		foreach($getList as $item)
		{
			$cantidad = $item->cantidad;
			$origen = $items->origen_bodega_id;
			$articulo = $item->articulo_id;
		}
		//actualizar bodegaOrigen
		$getBodegaOrigen = $this->bodegaArt->where("han_inventario_bodegas_articulos.articulo_id", "=" , $articulo_id)->where("han_inventario_bodegas_articulos.bodega_id" , "=" , $articulo)->get();

		foreach($getBodegaOrigen as $item)
		{
			$cantidadOrigen = $item->cantidad + $cantidad;
			$bodega_articulo_id = $item->bodega_articulo_id;
		}

		$this->bodegaArt->where("han_inventario_bodegas_articulos.bodega_articulo_id", "=" , $bodega_articulo_id)->update([
			"cantidad" => $cantidadOrigen,
		]);

		//delete
		$this->transfer->where(["transferencia_id" => $id])->delete();

		$responseMessage = "eliminado";

		$this->customResponse->is200Response($response , $responseMessage);



	}
}