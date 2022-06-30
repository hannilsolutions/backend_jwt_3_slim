<?php

$logDate = new DateTime();

return [

    "settings"=>[
        "displayErrorDetails"=>true,
        "logErrors"=>true,
        "logErrorDetails"=>true,
        "determineRouteBeforeAppMiddleWare"=>true
    ],
    "logger"	=> [
    	"name" => 'hannillog',
    	"path"	=> __DIR__."/logs/".$logDate->format('Y-m-d H-mm-ss')."app.log",
    ],
];