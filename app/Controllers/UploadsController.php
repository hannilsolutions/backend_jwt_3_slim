<?php

namespace App\Controllers;

use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;



class UploadsController
{

    protected  $customResponse;


    protected  $validator;

    public function  __construct()
    {
         $this->customResponse = new CustomResponse();


         $this->validator = new Validator();
    }

    public function uploads(Request $request,Response $response)
    {

        $this->validator->validate($request,[
           "id"=>v::notEmpty(),
           "url_img"=>v::notEmpty(),
           "destino"=>v::notEmpty(),
        ]);

        if($this->validator->failed())
        {
            $responseMessage = $this->validator->errors;
            return $this->customResponse->is400Response($response,$responseMessage);
        }

        $uploadedFiles = $request->getUploadedFiles();

        $uploadedFile = $uploadedFiles['url_img'];

        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {

            $filename = $this->moveUploadedFile(CustomRequestHandler::getParam($request,"destino") ,$uploadedFile);

            $response->write('uploaded ' . $filename . '<br/>');
        }

        $responseMessage = "cargado con exito";

        $this->customResponse->is200Response($response,$responseMessage);

    }

    function moveUploadedFile( $destino , UploadedFile $uploadedFile)
{
    $directory = __DIR__.'/../app/Files/'.$destino;
    $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
    $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php
    $filename = sprintf('%s.%0.8s', $basename, $extension);

    $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $filename);

    return $filename;
}


}