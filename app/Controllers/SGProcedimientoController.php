<?php
namespace App\Controllers;
 
use App\Models\SGGeneralidades;
use App\Models\SGProcedimiento;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator; 
class SGProcedimientoController{

    protected $generalidades;

    protected $procedimientos;

    protected $validator;

    protected $customResponse;

    public function __construct()
    {
        $this->customResponse = new CustomResponse();

        $this->generalidades = new SGGeneralidades();

        $this->validator = new Validator();

        $this->procedimientos = new SGProcedimiento();


    }


    public function generalidades_procedimientos(Request $request , Response $response)
    {
        $procedimientos = $this->generalidades->where(["tipo" => "Procedimiento"])->get();

        $listProcedimiento = [];

        if($procedimientos->count() > 0)
        {
            foreach($procedimientos as $item)
            {
             $listProcedimiento = json_decode($item->nombre);
            }
        }

        $this->customResponse->is200Response($response , $listProcedimiento);
    }

    public function save(Request $request , Response $response)
    {
        $this->validator->validate($request , [
			"id_user" => v::notEmpty(),
			"id_permiso" => v::notEmpty()

		]);

		if($this->validator->failed())
		{
			$responseMessage = $this->validator->errors;

			return	$this->customResponse->is400Response($response , $responseMessage);


		}

        $this->procedimientos->create([
            "id_user" => CustomRequestHandler::getParam($request , "id_user"),
            "id_permiso" => CustomRequestHandler::getParam($request , "id_permiso"),
            "procedimiento" => json_encode(CustomRequestHandler::getParam($request  , "procedimiento"))
        ]);

        $this->customResponse->is200Response($response , "Creado");
    }

    public function findByUserAndPermiso(Request $request , Response $response)
    {
        $this->validator->validate($request , [
			"id_user" => v::notEmpty(),
			"id_permiso" => v::notEmpty()

		]);

		if($this->validator->failed())
		{
			$responseMessage = $this->validator->errors;

			return	$this->customResponse->is400Response($response , $responseMessage);


		}
        $search = [];

        $list =  $this->procedimientos
                    ->where(["id_user" => CustomRequestHandler::getParam($request , "id_user")])
                    ->where(["id_permiso" => CustomRequestHandler::getParam($request , "id_permiso")])
                    ->get();
            if($list->count() > 0)
            {
                foreach($list as $item)
                {
                    $search = json_decode($item->procedimiento);
                }
            }
                    
        $this->customResponse->is200Response($response , $search);
    }

}


?>