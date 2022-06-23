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
        $secret = "d3fc3547346f0ef9cc47b9d5951912559bda2322ed3a2794d0ae49f76110dc61";

        $payload = [
          "jti"=>$email,
          "iat"=>$now,
          "exp"=>$future
        ];

        return JWT::encode($payload,$secret,"HS256");
    }

    public static function decodeToken($token)
    {
      $secret = "d3fc3547346f0ef9cc47b9d5951912559bda2322ed3a2794d0ae49f76110dc61";
      return JWT::decode($token , $secret , array("HS256"));
    }
}

?>