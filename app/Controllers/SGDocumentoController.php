<?php

namespace App\Controllers;

use App\Models\SGDocumentos; 
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator; 

class SGDocumentoController
{
	protected $documento;

	protected $customResponse;

	protected $validator;

	public function __construct()
	{
		$this->documento = new SGDocumentos();

		$this->customResponse = new CustomResponse();

		$this->validator = new Validator();
	}

	/**
	 * ENDPOINT POST save*/
	public function save (Request $request , Response $response)
	{
		$this->validator->validate($request , [
			"documento_poliza" => v::notEmpty(),
			"documento_inicio" => v::notEmpty(),
			"documento_fin" => v::notEmpty(),
			"documento_tipo" => v::notEmpty(),
			"referencia_id" => v::notEmpty(),


		]);

		if($this->validator->failed())
		{
			$responseMessage = $this->validator->errors;

			return $this->customResponse->is400Response($response , $responseMessage);
		}

		if($this->verifyExist(CustomRequestHandler::getParam($request , "documento_poliza")))
		{
			$responseMessage = "Poliza ya existe";

			return $this->customResponse->is400Response($response , $responseMessage);
		}


		$uploadedFiles = $request->getUploadedFiles();

        $uploadedFile = $uploadedFiles['file_uploads'];

        if ($uploadedFile->getError() === UPLOAD_ERR_OK) 
        {

        	$filename = $this->moveUploadedFile($request , $uploadedFile);

        	$responseMessage = "cargado";

        	$this->customResponse->is200Response($response , $responseMessage);
        }


	}

	public function moveUploadedFile($request , $uploadedFile)
	{
		$extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);

        $basename = bin2hex(random_bytes(8)); // see http://php.net/manual/en/function.random-bytes.php

        $filename = sprintf('%s.%0.8s', $basename, $extension);

        $destino = CustomRequestHandler::getParam($request , "documento_tipo");

        $referencia = CustomRequestHandler::getParam($request , "referencia_id");

        $path      = "/home/internet/public_html/apps/Files/".$destino."/".$referencia;

        if (!is_dir($path)) {

            mkdir($path, 0777, true);
        }

        $uploadedFile->moveTo("/home/internet/public_html/apps/Files/Documentos/$destino/$referencia/$filename");

        //$uploadedFile->moveTo("/var/www/html/Files/$destino/$filename");

        $this->documento->create([
                    "documento_url" => $path."/".$filename,
                    "documento_filename" => $filename,
                    "documento_tipo" => $destino,
                    "documento_nombre" => CustomRequestHandler::getParam($request , "documento_poliza"),
                    "documento_caducidad" => "SI",
                    "documento_inicio" => CustomRequestHandler::getParam($request , "documento_inicio"),
                    "documento_fin" => CustomRequestHandler::getParam($request , "documento_fin"),
                    "documento_estado" => "VIGENTE",
                    "referencia_id" => CustomRequestHandler::getParam($request , "referencia_id")

                ]);

        return $filename;
	}

	public function verifyExist($poliza)
	{
		$getPoliza = $this->documento->where("documento_nombre" , "=" , $poliza)->count();

		if ($getPoliza > 0) {
			
			return true;

		}else{

			return false;
		}
	}

	/**
	 * ENDPOINT POST consulta de documentos*/
	public function findByTipo(Request $request , Response $response)
	{
		$this->validator->validate($request , [
			"documento_tipo" => v::notEmpty(),
			"referencia_id" => v::notEmpty()
		]);

		if ($this->validator->failed()) {
			
			$responseMessage = $this->validator->errors;

			return $this->customResponse->is400Response($response , $responseMessage);
		}

		$documento_tipo = CustomRequestHandler::getParam($request , "documento_tipo");

		$referencia = CustomRequestHandler::getParam($request , "referencia_id");


		$getFindByTipo = $this->documento->where("documento_tipo" , "=" , $documento_tipo)
										->where("referencia_id" , "=" , $referencia)
										->get();

		$this->customResponse->is200Response($response , $getFindByTipo);
	}

	/**
	 * ENDPOINT GET URL*/
	public function getDocumentoUrl(Request $request , Response $response , $id)
	{
		try{
			$getInfo = $this->documento->where(["documento_id"=>$id])->get();
		///armar url
		foreach($getInfo as $item)
		{
			$url = "Files/Documentos/".$item->documento_tipo."/".$item->referencia_id."/".$item->documento_filename;
		}

		$this->customResponse->is200Response($response , $url);

		}catch(Exception $e)
		{
			$errorMessage = $e->getMessage();

			$this->customResponse->is400Response($response , $errorMessage);
		}
	}

}

?>