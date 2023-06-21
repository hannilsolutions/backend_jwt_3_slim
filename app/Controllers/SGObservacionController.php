<?php

namespace App\Controllers;

use App\Models\SGObservaciones;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;
use Psr\Http\UploadedFile;


class SGObservacionController
{
    private $customResponse;

    private $validator;

    private $observacion;

    public function __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->validator = new Validator();

        $this->observacion = new SGObservaciones();
    }

    //post save
    public function save(Request $request , Response $response)
    {
        $this->validator->validate($request , [

            "observacion" => v::notEmpty(),
            "id_usuario" => v::notEmpty(),
            "id_permiso" => v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;

            return $this->customResponse->is400Response($response , $responseMessage);
        }

        $this->observacion->create([
            "observacion" => CustomRequestHandler::getParam($request , "observacion"),
            "id_usuario" => CustomRequestHandler::getParam($request , "id_usuario"),
            "id_permiso" => CustomRequestHandler::getParam($request , "id_permiso")
        ]);

        $responseMessage = "creado";

        $this->customResponse->is200Response($response , $responseMessage);

    }

    //GET BY IDPERMISO
    public function findByIdPermiso(Request $request , Response $response , $id)
    {
        $list = $this->observacion->selectRaw('han_sg_permisos_observaciones.id_observacion , 
        han_sg_permisos_observaciones.observacion,
        han_sg_permisos_observaciones.created_at,
        han_sg_permisos_observaciones.url_img,
        han_sg_permisos_observaciones.id_permiso,
        users.user')
        ->join("users" , "users.id" , "=" , "han_sg_permisos_observaciones.id_usuario")
        ->where(["han_sg_permisos_observaciones.id_permiso" => $id])
        ->get();

        $this->customResponse->is200Response($response , $list);
    }

    //delete
    public function delete(Request $request , Response $response , $id)
    {
        $this->observacion->where(["id_observacion"=>$id])->delete();

        $responseMessage = "Eliminado con éxito";

        $this->customResponse->is200Response($response , $responseMessage);
    }

    //cargar imagen
    //post
    public function imagen(Request $request , Response $response)
    {
        $this->validator->validate($request , [

            "id_permiso" => v::notEmpty(),
            "id_usuario" => v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;

            return $this->customResponse($response , $responseMessage);

        }

        $uploadedFiles = $request->getUploadedFiles();

        $file = $uploadedFiles['img'];

        try{

            if($file->getError() === UPLOAD_ERR_OK)
            {
                $filename = $this->moveToImg(CustomRequestHandler::getParam($request , "id_permiso") , $file);

                $this->observacion->create([
                    "id_usuario" => CustomRequestHandler::getParam($request , "id_usuario"),
                    "id_permiso" => CustomRequestHandler::getParam($request , "id_permiso"),
                    "url_img"   => $filename
                ]);

                $responseMessage = "creado";

                $this->customResponse->is200Response($response , $responseMessage);
            }
        }catch(Exception $e)
        {
              $this->customResponse->is400($response , $e->getMessage());
        }

    }

    private function moveToImg($idPermiso , $file)
    {
        $extension = pathinfo($file->getClientFilename() , PATHINFO_EXTENSION);

        $basename = bin2hex(random_bytes(8));

        $filename = sprintf('%s.%0.8s', $basename, $extension);

        $path = '/home/internet/public_html/apps/Files/permisos/'.$idPermiso;

        if(!is_dir($path))
        {
            mkdir($path , 0777 , true);
        }

        $file->moveTo("/home/internet/public_html/apps/Files/permisos/$idPermiso/$filename");

        return $filename;
    }
}

?>