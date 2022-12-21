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

     

    public function listKardex(Request $request , Response $response , $id)
    {

     try{

        $this->bodegaArt->selectRaw("han_inventario_bodegas_articulos.bodega_articulo_id,
                                             han_inventario_bodegas_articulos.articulo_id,
                                             han_inventario_bodegas_articulos.cantidad,
                                             han_inventario_articulos.articulo_nombre,
                                             han_inventario_articulos.articulo_categoria")
                                          ->join("han_inventario_articulos" , "han_inventario_articulos.articulo_id", "=" ,"han_inventario_bodegas_articulos.articulo_id")
                                          ->where(["han_inventario_bodegas_articulos.bodega_id" => $id])->get();

         $this->customResponse->is200Response($response , $getList);

     }catch(QueryException $e)
     {
         $this->customResponse->is400Response($response , $e->getMessage());
     }

    }

  
}
 
?>