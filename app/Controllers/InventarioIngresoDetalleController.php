<?php

namespace App\Controllers;

use App\Models\InventarioIngresoDetalle;
use App\Models\InventarioBodegaArticulo;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class InventarioIngresoDetalleController  
{ 
	protected  $customResponse;

   	protected  $detalle;

    protected  $validator;

    protected $bodegas;

        public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->detalle = new InventarioIngresoDetalle();

         $this->validator = new Validator();

         $this->bodegaArt = new InventarioBodegaArticulo();
    }


    /**
 * ENDPOINT POST save*/
    public function save(Request $request,Response $response)
    {

        $this->validator->validate($request,[
            "articulo_id"=> v::notEmpty(),
     		"ingreso_id"=> v::notEmpty(),
     		"ingreso_detalle_cantidad"=> v::notEmpty(),
     		"ingreso_detalle_compra"=> v::notEmpty(),
     		"ingreso_detalle_venta"=> v::notEmpty(),
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }



        try{

            $this->detalle->create([
            "articulo_id"=>CustomRequestHandler::getParam($request,"articulo_id"),
            "ingreso_id"=>CustomRequestHandler::getParam($request,"ingreso_id"),
            "ingreso_detalle_cantidad"=>CustomRequestHandler::getParam($request,"ingreso_detalle_cantidad"),
            "ingreso_detalle_compra"=>CustomRequestHandler::getParam($request,"ingreso_detalle_compra"),
            "ingreso_detalle_venta"=>CustomRequestHandler::getParam($request,"ingreso_detalle_venta")
        ]);

        $udapted = $this->updatedBodegaArticulo($request);

       



        $responseMessage = "creado";

        $this->customResponse->is200Response($response,$responseMessage);

        }catch(QueryException $e)
        {
            $this->customResponse->is400Response($response , $e);
        }

    }

   

    /**
     * ENDPOINT get findById*/
    public function findDetalleByIngresoId(Request $request , Response $response , $id)
    {
    	try{

            $get = $this->detalle->selectRaw("  art.articulo_nombre , 
                                            han_inventario_ingresos_detalles.ingreso_detalle_id , 
                                            han_inventario_ingresos_detalles.ingreso_detalle_cantidad,
                                            han_inventario_ingresos_detalles.articulo_id,
                                            sum(han_inventario_ingresos_detalles.ingreso_detalle_cantidad * han_inventario_ingresos_detalles.ingreso_detalle_compra ) as compra,
                                            sum(han_inventario_ingresos_detalles.ingreso_detalle_cantidad * han_inventario_ingresos_detalles.ingreso_detalle_venta) as venta ")
                                            ->join("han_inventario_articulos as art" , "art.articulo_id" , "=" , "han_inventario_ingresos_detalles.articulo_id")
                                            ->where(["han_inventario_ingresos_detalles.ingreso_id" => $id])                           
                                            ->groupBy("art.articulo_id")
                                            ->orderBy("han_inventario_ingresos_detalles.ingreso_detalle_id ")->get();

            $this->customResponse->is200Response($response , $get);

        }catch(QueryException $e)
        {
            $this->customResponse->is200Response($response , $e);
            
        }
    }


    /**
     * ENDPOINT GET sumByIngresoId*/
    public function sumByIngresoId(Request $request , Response $response , $id)
    {
    	try{

            $getSum = $this->detalle->selectRaw("sum(han_inventario_ingresos_detalles.ingreso_detalle_cantidad * han_inventario_ingresos_detalles.ingreso_detalle_compra) as compra")->where(["han_inventario_ingresos_detalles.ingreso_id" => $id])->get();

        $this->customResponse->is200Response($response , $getSum);

        }catch(QueryException $e)
        {
            $this->customResponse->is200Response($response , $e);
        }
    }

    /**
     * actualizar bodegaarticulo*/
    public   function updatedBodegaArticulo($request)
    {

        //validamos si existe el bodega articulo,
        $getcount = $this->bodegaArt
                                    ->where("articulo_id" , "=" , CustomRequestHandler::getParam($request , "articulo_id"))
                                    ->where("bodega_id" , "=" , CustomRequestHandler::getParam($request , "bodega_id"))
                                    ->count();

        if($getcount > 0)
        {
            //actualizamos
            $info = $this->bodegaArt
                                    ->where("articulo_id" , "=" , CustomRequestHandler::getParam($request , "articulo_id"))
                                    ->where("bodega_id" , "=" , CustomRequestHandler::getParam($request , "bodega_id"))
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
            try
            {
                    $this->bodegaArt->create([ 
                "articulo_id"=> CustomRequestHandler::getParam($request , "articulo_id"),
                "bodega_id"=> CustomRequestHandler::getParam($request , "bodega_id"),
                "cantidad"=> CustomRequestHandler::getParam($request , "ingreso_detalle_cantidad"),
            ]);
                }catch(QueryException $e)
                {
                    echo $e;
                }
        
        }
    }





}

?>