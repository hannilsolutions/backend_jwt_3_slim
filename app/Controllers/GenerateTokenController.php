<?php


namespace App\Controllers;



#use App\Interfaces\SecretKeyInterface;
use \Firebase\JWT\JWT;

class GenerateTokenController #implements SecretKeyInterface
{

    public static function generateToken($email)
    {
        $now = time();
        $future = strtotime('+12 hour',$now);
        $secret = SECRET_PASSWORD;

        $payload = [
          "jti"=>$email,
          "iat"=>$now,
          "exp"=>$future
        ];

        return JWT::encode($payload,$secret,"HS256");
    }

    public static function decodeToken($token)
    {
      $secret = SECRET_PASSWORD;
      return JWT::decode($token , $secret , array("HS256"));
    }
}

?>