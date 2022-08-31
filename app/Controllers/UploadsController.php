<?php

namespace App\Controllers;

use App\Models\Han_Uploads;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;
use Psr\Http\UploadedFile;



class UploadsController
{

    protected  $customResponse;

    protected $uploads;

    protected  $validator;

    public function  __construct()
    {
         $this->customResponse = new CustomResponse();

         $this->uploads = new Han_Uploads();

         $this->validator = new Validator();
    }

    /*
    *POST file */
    

    public function uploads(Request $request,Response $response)
    {

        $this->validator->validate($request,[
           "categoria"=>v::notEmpty(),
           "fecha" => v::notEmpty()
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;

            return $this->customResponse->is400Response($response,$responseMessage);
        }

        $uploadedFiles = $request->getUploadedFiles();

        $uploadedFile = $uploadedFiles['file_uploads'];

        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {

           $filename = $this->moveUploadedFile(CustomRequestHandler::getParam($request,"categoria") , $uploadedFile , CustomRequestHandler::getParam($request,"fecha"));
            //$destino = CustomRequestHandler::getParam($request , "categoria");

            //$directory = __DIR__."/Files/$destino";

           // $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);

            //$basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
        
           // $filename = sprintf('%s.%0.8s', $basename, $extension);

           // $uploadedFile->moveTo("/home/internet/public_html/apps/Files/$destino/$filename");

            //$response->write('uploaded ' . $filename . '<br/>');
            

            $responseMessage = $filename;
        }

        //$responseMessage = "cargado con exito";

        $this->customResponse->is200Response($response,$responseMessage);

    }

    function moveUploadedFile( $destino , $uploadedFile , $fecha)
    {
        $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);

        $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php

        $filename = sprintf('%s.%0.8s', $basename, $extension);

        $path      = "/home/internet/public_html/apps/Files/".$destino;

        if (!is_dir($path)) {

            mkdir($path, 0777, true);
        }

        $uploadedFile->moveTo("/home/internet/public_html/apps/Files/$destino/$filename");

        //$uploadedFile->moveTo("/var/www/html/Files/$destino/$filename");

        $this->uploads->create([
                    "titulo" => $filename,
                    "categoria" => $destino,
                    "tipo"      => $extension,
                    "fecha"     => $fecha
                ]);

        return $filename;
    }

    /**
     * ENDPOINT GET list by category with limit variable
     * */

    public function listCategoria(Request $request , Response $response , $count , $categoria)
    {
        $getList = $this->uploads->where(["categoria" => $categoria])->limit($count["count"])->get();

        $this->customResponse->is200Response($response , $getList);
    }

    /**
     * ENDPOINT DELETE eliminar upload and archivo
     * */
    public function delete(Request $request , Response $response , $id)
    {
        $this->validator->validate($request , [
            "name" => v::notEmpty(),
            "category" => v::notEmpty()
        ]);

        if ($this->validator->failed()) {

            $responseMessage = $this->validator->errors;

            return $this->customResponse->is400Response($response , $responseMessage);
        }
        $folder = CustomRequestHandler::getParam($request  , "category");

        $filename = CustomRequestHandler::getParam($request , "name");

        $pathFile = "/home/internet/public_html/apps/Files/".$folder."/".$filename;

        if (file_exists($pathFile)) {
            #comienza a eliminar archivo
            if(!unlink($pathFile))
            {
                $responseMessage = "Error eliminando el archivo";

                return $this->customResponse->is400Response($response , $responseMessage);
            }

        }else {

            $responseMessage = "archivo no encontrado";

            return $this->customResponse->is400Response($response , $responseMessage);
        }

        $this->uploads->where(["id" => $id])->delete();

        $responseMessage = "eliminado";

        $this->customResponse->is200Response($response , $responseMessage);

    }




   


}

?>