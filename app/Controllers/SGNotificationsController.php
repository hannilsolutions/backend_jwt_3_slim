<?php


namespace App\Controllers;

use App\Models\Han_Notifications;
use App\Requests\CustomRequestHandler;
use App\Response\CustomResponse;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;
use Respect\Validation\Validator as v;
use App\Validation\Validator;

class SGNotificationsController {

    private $validador;

    private $customResponse;

    private $notifications;

    public function __construct()
    {
        $this->validator = new Validator();

        $this->customResponse = new CustomResponse();

        $this->notifications = new Han_Notifications();
    }

    public static function create($array)
    {
        $notifications = new Han_Notifications();
        
        $notifications->create([
            "id_user" => $array->id_user,
            "id_referencia" => $array->id_referencia,
            "estado" => 1,
            "comentario" => $array->comentario,
            "icon" => $array->icon,
            "url" => $array->url,
            "title" => $array->title
        ]);
    }

    /**LIST BY ID_USER -> ULTIMOS 5 */
    public function list_by_five(Request $request , Response $response)
    {
            $this->validator->validate($request, [
                "id_user" => v::notEmpty(),
                "count" => v::notEmpty()
            ]);

            if($this->validator->failed())
            {
                $responseMessage = $this->validator->errors;

                return $this->customResponse->is400Response($response , $responseMessage);
            }

            $count = CustomRequestHandler::getParam($request , "count");
            $id = CustomRequestHandler::getParam($request , "id_user");
             

           $list = $this->notifications->where(["id_user" => $id])
                                        ->orderBy("created_at" , "desc")
                                        ->take($count)
                                        ->get();
            $this->customResponse->is200Response($response , $list);
    }

    /** */
    public function updated( Request $request , Response $response , $id)
    {
        $this->notifications->where(["id" => $id])->update([
            "estado" => 2
        ]);


        $this->customResponse->is200Response($response , "actualizado");
    }

}

?>