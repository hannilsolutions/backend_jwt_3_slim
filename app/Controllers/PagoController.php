<?php

namespace App\Controllers;

use App\Models\Pago;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class PagoController
{

    protected  $customResponse;

    protected  $pago;

    protected  $validator;

    public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->pago = new Pago();

         $this->validator = new Validator();
    }

    /**
     * buscar por dias date("Y-m-d")
     * y descargue 0 
     * estado_descargue = 1 ya bajado a controlmas ,*/
    public function buscarEstadoCargue(Request $request , Response $response)
    {
        $hoy = date("Y-m-d");

        try{
            $get = $this->pago->where("fecha_recaudo" , "=" , $hoy)->where("estado_descargue" , "!=" , 1)->get();

            $this->customResponse->is200Response($response , $get);

        }catch(Exception $e)
        {
            $this->customResponse->is400Response($response , $e->getMessage());
        }
    }

    /**
     * UPDATED ESTADO_DESCARGUE PACHT*/
    public function updatedDescargue(Request $request , Response $response , $id)
    {
        try{

            $this->pago->where(["id" => $id])->update([
                "estado_descargue" => 1
            ]);

            $responseMessage = "actualizado";

            $this->customResponse->is200Response($response , $responseMessage);

        }catch(Exception $e)
        {
            $this->customResponse->is400Response($response , $e->getMessage());
        }
    }

    /*
    *POST findByBetwenn
    */
    public function findByBetween(Request $request , Response $response)
    {
        $this->validator->validate($request , [
            "valor1" => v::notEmpty(),
            "valor2" => v::notEmpty()
        ] );

        if ($this->validator->failed())
         {
           $responseMessage = $this->validator->errors;

           return $this->customResponse->is400Response($response , $responseMessage);
        }

             $getFindByBetween =  $this->pago
                        ->whereBetween('fecha_recaudo', [CustomRequestHandler::getParam($request , "valor1"), CustomRequestHandler::getParam($request , "valor2")])
                        ->where('reversado' , '!=' , 1)
                        ->get();
            $this->customResponse->is200Response($response , $getFindByBetween);
    }

    #guardar datos
    public function save(Request $request, Response $response){

        #validar campos vacios
        $this->validator->validate($request,[
            "fecha_recaudo"=>v::notEmpty(),
            "hora_recaudo"=>v::notEmpty(),
            "id_transaccion"=>v::notEmpty(),
            "cc_asesor"=>v::notEmpty(),
            "nombre_asesor"=>v::notEmpty(),
            "codigo_pto_vta"=>v::notEmpty(),
            "nombre_pto_vta"=>v::notEmpty(),
            "nombre_convenio"=>v::notEmpty(),
            "numero_referencia"=>v::notEmpty(),
            "valor_total"=>v::notEmpty(),
            "cc_cliente"=>v::notEmpty(),
            "nombre_cliente"=>v::notEmpty()
            
         ]);
         if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }
        #validamos si ya existe un registro por id_transaccion

        if($this->verifyExistTransaccion(CustomRequestHandler::getParam($request, "id_transaccion")))
        {
            $responseMessage = "id_transaccion ya creada";
            
            return $this->customResponse->is400Response($response , $responseMessage);
        }

        $this->pago->create([
            "fecha_recaudo"=>CustomRequestHandler::getParam($request,"fecha_recaudo"),
            "hora_recaudo"=>CustomRequestHandler::getParam($request,"hora_recaudo"),
            "id_transaccion"=>CustomRequestHandler::getParam($request,"id_transaccion"),
            "cc_asesor"=>CustomRequestHandler::getParam($request,"cc_asesor"),
            "nombre_asesor"=>CustomRequestHandler::getParam($request,"nombre_asesor"),
            "codigo_pto_vta"=>CustomRequestHandler::getParam($request,"codigo_pto_vta"),
            "nombre_pto_vta"=>CustomRequestHandler::getParam($request,"nombre_pto_vta"),
            "nombre_convenio"=>CustomRequestHandler::getParam($request,"nombre_convenio"),
            "numero_referencia"=>CustomRequestHandler::getParam($request,"numero_referencia"),
            "valor_total"=>CustomRequestHandler::getParam($request,"valor_total"),
            "cc_cliente"=>CustomRequestHandler::getParam($request,"cc_cliente"),
            "nombre_cliente"=>CustomRequestHandler::getParam($request,"nombre_cliente")
         ]);
 
         $responseMessage = "creado satisfactoriamente";
 
         return $this->customResponse->is200Response($response,$responseMessage);
    }

    #consulta de todos los registros
    public function all(Request $request , Response $response)
    {
        $pago = $this->pago->get();

        return $this->customResponse->is200Response($response , $pago);
    }

    #reversar un pago 
    public function reversarPago(Request $request , Response $response , $id)
    { #recibe el parametro id de id_transacción
        $this->validator->validate($request,[
            "fecha_reversado"=>v::notEmpty(),
            "comentario_reversado"=>v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }
        #validamos si el id de la transaccion existe
        $verifyIdTrasaccion = $this->verifyExistTransaccion($id);
        if($verifyIdTrasaccion==false){
            #en caso que el id_transaccion no existe se envia msm que no existe el id_trasaccion
            $responseMessage ="el id_transaccion no existe";
            return $this->customResponse->is400Response($response,$responseMessage);
        }
        #buscar el id_transaccion para traer el id de la tabla
        $transaccion = $this->pago->where(["id_transaccion" => $id])->first();#para retornar un objeto y no 
        #una coleccion de objetos

        #una vez cargado se realiza la consulta o actualización a dicho id recuperado
        $this->pago->where(['id'=>$transaccion->id])->update([
            "fecha_reversado"=>CustomRequestHandler::getParam($request,"fecha_reversado"),
            "comentario_reversado"=>CustomRequestHandler::getParam($request,"comentario_reversado"),
            "reversado"=>"1",
        ]);
        $responseMessage = "pago reversado";

        $this->customResponse->is200Response($response,$responseMessage);
    }

    #retornar un pago por su id transaccion
    public function findOne(Request $request , Response $response , $id)
    {   
        #validamos si existe el id transaccion
        $count = $this->pago->where(["id_transaccion" => $id])->count();
        if($count > 0){
            #si el resultado es diferente de cero significa que si existe 
            #por tranto se procede a consultar el registro y enviar un 200
            $pago = $this->pago->where(["id_transaccion" => $id])->get();
            return $this->customResponse->is200Response($response , $pago);
        }
        #al no entonctrar nada se procede a enviar un 400 con el error
        $responseMessage = "sin registros";
        return $this->customResponse->is400Response($response , $responseMessage);
    }
    #count validar si existe un id de pago
    public function veriftExistIdPago($id){
        $count = $this->pago->where(["id" => $id])->count();
        if($count==0)
        {
            return false;
        }

        return true;
    }
    #validamos si una transaccion ya existe
    public function verifyExistTransaccion($transaccion)
    {
        $count = $this->pago->where(["id_transaccion"=>$transaccion])->count();
        if($count==false)
        {
            return false;
        }
        return true;
    }
 #suma por mes 
    public function sumaMes(Request $request , Response $response)
    {
         
           $suma = $this->pago->selectRaw('month(fecha_recaudo) as mes, format(sum(valor_total),2) as valor')
                            ->where('reversado','<','1')
                            ->groupBy('mes')
                            ->get(); 
        return $this->customResponse->is200Response($response , $suma);
    }

}