<?php

namespace App\Controllers;
 
use App\Models\ContratoGps;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;

class ContratoGPSController
{
    protected $customResponse;

    protected $contratoGps;

    protected $validator;

    public function __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->contratoGps = new ContratoGps();

        $this->validator =  new Validator();
    }

    /**
     * contratoGps -> latitud - longitud  
     * post
     */
       

    public function save(Request $request , Response $response)
    {
        $this->validator->validate($request,[
            "id_contrato"   =>  v::notEmpty(),
            "key"           =>  v::notEmpty(),
            "longitud"      =>  v::notEmpty(),
            "latitud"       =>  v::notEmpty()
         ]);
 
         if($this->validator->failed())
         {
             $responseMessage = $this->validator->errors;
             return $this->customResponse->is400Response($response,$responseMessage);
         }
         $getVerifyKey = $this->verifyKey(CustomRequestHandler::getParam($request , "key"));

         //validar si existe error en el token enviado desde la app
         if($getVerifyKey != true)
         {
            $responseMessage = "error key";
            return $this->customResponse->is400Response($response , $responseMessage);
         }

         //validar si existe ya el contrato creado
         $verifyExist = $this->verifyExist(CustomRequestHandler::getParam($request , "id_contrato")); 
         if($verifyExist != false)
         {
            $responseMessage = "Actualizado con anterioridad";
            return $this->customResponse->is400Response($response , $responseMessage);
         }
         //se crea contrato
         $this->contratoGps->create([
        "id_contrato"=>CustomRequestHandler::getParam($request,"id_contrato"),
         "latitud"=>CustomRequestHandler::getParam($request,"latitud"),
         "longitud"=>CustomRequestHandler::getParam($request,"longitud"),
         ]);
 
         $responseMessage = "creado";
 
         $this->customResponse->is200Response($response,$responseMessage);
    }
    /**
     * function valida key enviado desde la app, sha256
     */
    public function verifyKey($key)
    {
        //HannilSolutions
        $k = "3e1d7ed98e94366975582f41f77a0bc9442a288da87d164bdc9fef66e57de70f";
        if($key == $k)
        {
            return true;
        }else{

            return false;
        }
    }
    /**
     * function valida si existe contrato ya creado
     */
    public function verifyExist($contrato)
    {
        $count = $this->contratoGps->where(["id_contrato"=>$contrato])->count();

        if($count==false)
        {
            return false;
        }
        return true;
    }

    /**
     * post contratos buscados por nombre de barrios consulta a controlmas y busca en servidor
     int en tabla de cargados
     */
    public function getContratoGps(Request $request , Response $response)
    {

        $this->validator->validate($request,[
            "barrio"   =>  v::notEmpty(),
         ]);
 
         if($this->validator->failed())
         {
             $responseMessage = $this->validator->errors;
             return $this->customResponse->is400Response($response,$responseMessage);
         }

        $getContratosByBarrioControl = $this->getContratosByBarrioControl(CustomRequestHandler::getParam($request , "barrio"));

        

        if($getContratosByBarrioControl != "sin registros")
        {
            $responseMessage    = $this->contratoGps
                                    ->whereIn('id_contrato' , $getContratosByBarrioControl)
                                    ->get();
        }else{
            $responseMessage = "error buscando";
        }

       $this->customResponse->is200Response($response , $responseMessage);
    }

    /**
     * get list municipios segun el departamento
     *  */
    public function findMunicipios(Request $request , Response $response , $id)
    {
        
        $findMunicipios = $this->controlmasFindByMunicipio($id["id"]);
        
        $responseMessage = $findMunicipios;
        
        $this->customResponse->is200Response($response,$responseMessage);

    }

    /*
    *get lista barrios segun el municipio 1085
    */
    public function findBarrios(Request $request , Response $response , $id)
    {
        $findBarrios = $this->controlmasFindByBarrio($id["id"]);

        $responseMessage    = $findBarrios;

        $this->customResponse->is200Response($response , $responseMessage);
    }
     

    /**function consulta controlmas municipios */
    public function controlmasFindByMunicipio($id)
    {
        $data = array(
            "departamento"=> $id,
            "key" => 'f24f0aaa81db035965e65f60c5e54c41',
            "m" => 4,
            "title" => 'findByMunicipio'
        );
        $ch =   curl_init("http://131.221.41.20:8050/api/api_internet/v2/public/");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = json_decode(curl_exec($ch));
        curl_close($ch);
        if($response->success==false) {
                return false;
        }else{
                return $response->data;
        }
    }

    /**function consulta controlmas Barrios */
    public function controlmasFindByBarrio($id)
    {
        $data = array(
            "municipio"=> $id,
            "key" => 'f24f0aaa81db035965e65f60c5e54c41',
            "m" => 4,
            "title" => 'findByBarrio'
        );
        $ch =   curl_init("http://131.221.41.20:8050/api/api_internet/v2/public/");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = json_decode(curl_exec($ch));
        curl_close($ch);
        if($response->success==false) {
                return false;
        }else{
                return $response->data;
        }
    }

    /**
    function consulta controlmas de contratos x barrio
    */
    public function getContratosByBarrioControl($barrio)
    {
        $data = array(
            "barrio"=> $barrio,
            "key" => 'f24f0aaa81db035965e65f60c5e54c41',
            "m" => 4,
            "title" => 'findContratoByBarrio'
        );
        $ch =   curl_init("http://131.221.41.20:8050/api/api_internet/v2/public/");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $response = json_decode(curl_exec($ch));
        curl_close($ch);
        if($response->success==false) {
                return false;
        }else{
                return $response->data;
        }

    }

    /*
    * POST findByBetween buscar en un rangos de fechas
    */

    public function findByBetween(Request $request , Response $response)
    {
        $this->validator->validate($request , [
            "valor1" => v::notEmpty(),
            "valor2" => v::notEmpty()
        ]);

        if ($this->validator->failed()) {
            
            $responseMessage = $this->validator->error;

            return $this->customResponse->is400Response($response, $responseMessage);
        }

        $responseMessage = $this->contratoGps
                                ->whereBetween('fecha', [CustomRequestHandler::getParam($request , "valor1"), CustomRequestHandler::getParam($request , "valor2")])
                                ->get();
        $this->customResponse->is200Response($response , $responseMessage);
    }
     
}


?>