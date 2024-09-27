<?php

namespace App\Controllers;

use App\Models\SGEmpleadoAptitud;
use App\Models\SGGeneralidades;
use App\Models\SGPermiso;
use App\Models\SGConfiguration;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class SGEmpleadoAptitudController{

    protected $empleadoAptitud;

    private $validator;

    private $generalidades;

    protected $customResponse;

    protected $permiso;
    
    protected $configuration;


    public function __construct()
    {
        $this->empleadoAptitud = new SGEmpleadoAptitud();

        $this->validator = new Validator();

        $this->generalidades = new SGGeneralidades();

        $this->customResponse = new CustomResponse();

        $this->permiso = new SGPermiso();

        $this->configuration = new SGConfiguration();
    }

    public function validate(Request $request , Response $response)
    {
        $this->validator->validate($request , [
            "id_permiso" => v::notEmpty(),
            "id_user" => v::notEmpty()
        ]);
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;

            return $this->customResponse->is400Response($response , $responseMessage);
        }
        $id_user = CustomRequestHandler::getParam($request , "id_user");

        $id_permiso = CustomRequestHandler::getParam($request , "id_permiso");

        $permiso = $this->permiso->where(["id_permiso" => $id_permiso])->get();
        if($permiso->count() > 0)
        {
             
            $json = "";
            foreach($permiso as $item)
            {
                if(!empty($item->json))
                {
                    $json = json_decode($item->json);
                }
            }
            if(empty($json))
            {
              return  $this->customResponse->is400Response($response , "Json mal formado");
            }

            $this->validate_aptitud($response , $id_permiso , $id_user , $json);
             


        }else{

            $this->customResponse->is400Response($response , "No found");
        }
        

    }

    private function validate_aptitud(Response $response, $id_permiso , $id_user , $json)
    {
                $query = $this->empleadoAptitud->selectRaw("han_sg_empleado_aptitud.* , han_sg_generalidades.nombre")
                                ->join("han_sg_generalidades" , "han_sg_generalidades.id_generalidades" , "=" , "han_sg_empleado_aptitud.id_generalidades")
                                ->where(["han_sg_empleado_aptitud.id_permiso"=>$id_permiso])->where(["han_sg_empleado_aptitud.id_user"=> $id_user])->get();
                $errors = false;
                $preguntas = [];
                foreach($query as $item)
                {
                    if($item->respuesta == "Si")
                    {
                        $errors = true;
                        $preguntas["preguntas"] = $item->nombre;
                    }
                }
                // Caso 1: Si hay errores y no están validadas las preguntas
                if($errors && !$json->preguntas && !$json->validate)
                {                    
                    $json->validate = false;
                    $json->preguntas = true;
                    $this->permiso->where(["id_permiso"=>$id_permiso])->update([
                    "json" => json_encode($json)
                    ]);
                    //send_mail();
                   $configuration = $this->configuration->where(["code" => "html_condicion_actual"])->get();
                    $html  ="";
                    foreach($configuration as $item)
                    {
                        $html = $item->value;
                    }
                    $reemplazar = [
                        "{usuario}" => "Segurito",
                        "{preguntas}" => json_encode($preguntas),
                        "{id_permiso}" => $id_permiso
                    ];
                    $plantilla = str_replace(array_keys($reemplazar) , array_values($reemplazar) , $html);
                    if(!sendMail($plantilla , "seguridadst@internetinalambrico.com.co")){

                        return $this->customResponse->is400Response($response , "Error enviando correo"); 
                    }
                    return $this->customResponse->is422Response($response , $json);               
                }
                
                 // Caso 2: Si no hay errores
                if(!$errors){
                    $json->validate = true;
                    $json->preguntas = true;
                    $this->permiso->where(["id_permiso"=>$id_permiso])->update([
                    "json" => json_encode($json)
                    ]);
                     
                    return $this->customResponse->is200Response($response , $json);
              
                }

                // Caso 3: Si hay errores pero ya se han validado las preguntas
                if($errors && $json->preguntas && $json->validate)
                {
                    
                    return $this->customResponse->is200Response($response , $json);
                
                }
                
                // Caso 4: Si hay errores y las preguntas no están validadas
                if($errors && $json->preguntas && !$json->validate)
                {
                     
                    return $this->customResponse->is422Response($response , $json);
                }


                 
    }

    private function sendMail($plantilla , $destination)
	{
		//// Para enviar un correo HTML, debe establecerse la cabecera Content-type
		$cabeceras  = 'MIME-Version: 1.0' . "\r\n";
		$cabeceras .= 'Content-type: text/html; charset=UTF-8' . "\r\n";

		// Cabeceras adicionales
		$cabeceras .= 'From: HannilPro <sistemas@hannilsolutions.com>' . "\r\n";

		// Enviarlo

		$subject = 'Condición Actual';
		$msm = $plantilla;
		$msm = wordwrap($msm , 70);

		if(!mail($destination ,$subject , $msm , $cabeceras))
		{
			return false;
		}

		return true;

	
	}

    public function find_by_id_permiso_aptitud(Request $request , Response $response , $id)
    {
        $list = $this->empleadoAptitud->selectRaw("han_sg_generalidades.nombre , han_sg_empleado_aptitud.respuesta")
                                ->join("han_sg_generalidades" , "han_sg_generalidades.id_generalidades" , "=" , "han_sg_empleado_aptitud.id_generalidades")
                                ->where(["han_sg_empleado_aptitud.id_permiso_aptitud" => $id])->get();
        return $this->customResponse->is200Response($response , $list);                        
    }

    public function update_aptitud(Request $request , Response $response)
    {
        $this->validator->validate($request , [
            "id_empleado_aptitud" => v::notEmpty(),
            "respuesta" => v::notEmpty()
        ]);
        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;

            return $this->customResponse->is400Response($response , $responseMessage);
        }
        $this->empleadoAptitud->where(["id_empleado_aptitud" => CustomRequestHandler::getParam($request , "id_empleado_aptitud")])->update([
            "respuesta" => CustomRequestHandler::getParam($request , "respuesta")
        ]);

        return $this->customResponse->is200Response($response , "actualizado");
    }

    public function find_init_aptitud(Request $request ,  Response $response)
    {
        $this->validator->validate($request , [
            "id_permiso" => v::notEmpty(),
            "id_user" => v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;

            return $this->customResponse->is400Response($response , $responseMessage);
        }

        $id_user = CustomRequestHandler::getParam($request , "id_user");

        $id_permiso = CustomRequestHandler::getParam($request , "id_permiso");

        $query = $this->find_generalidades_by_empleado($id_permiso , $id_user);
         
        $permiso  = $this->permiso->where(["id_permiso" => $id_permiso])->get();
        $json  = "";
        foreach($permiso as $item)
        {
            if(!empty($item->json)){
                $json = json_decode($item->json);
            }
        }
        $responseMessage = new \stdClass(); 

        if($query->count() > 0)
        {
            //print_r($query);
            $responseMessage->id_permiso = $id_permiso;
            $responseMessage->json = $json;
            $responseMessage->preguntas = $query;
            return $this->customResponse->is200Response($response , $responseMessage);

        }else{
            
           $this->create($id_permiso , $id_user );

            $query2 = $this->find_generalidades_by_empleado($id_permiso , $id_user);
            $responseMessage->id_permiso = $id_permiso;
            $responseMessage->json = $json;
            $responseMessage->preguntas = $query2;
            return $this->customResponse->is200Response($response , $responseMessage);
        }
    }
    /**
     * FIND GENERALIDADES
     */

    private function find_generalidades_by_empleado($id_permiso , $id_user)
    {
         
        return $this->empleadoAptitud->selectRaw(" han_sg_empleado_aptitud.id_aptitud , han_sg_empleado_aptitud.id_permiso, han_sg_empleado_aptitud.id_user , han_sg_empleado_aptitud.id_generalidades 
        , han_sg_empleado_aptitud.respuesta , han_sg_generalidades.nombre ")
                        ->join("han_sg_generalidades" , "han_sg_generalidades.id_generalidades" , "=" , "han_sg_empleado_aptitud.id_generalidades")
                        ->where("han_sg_empleado_aptitud.id_permiso" , "=" ,  $id_permiso)
                        ->where("han_sg_empleado_aptitud.id_user" ,"=", $id_user)
                        ->get();
    }
    /**
     * CREATE ALL
     */

    private function create($id_permiso , $id_user)
    {
        $generalidades = $this->generalidades->where(["item"=>"Preguntas"])->where(["estado"=>1])->get();

        if($generalidades->count() > 0)
        {
            foreach($generalidades as $item)
            {
                $this->empleadoAptitud->create([
                    "id_permiso" => $id_permiso,
                    "id_user" => $id_user,
                    "id_generalidades" => $item->id_generalidades          
                ]);
            }
        }
    }
}


?>