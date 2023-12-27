<?php
/**CREATE TABLE `internet_pagos`.`han_notifications` (
  `id` INT NOT NULL,
  `id_user` INT NULL,
  `estado` INT NULL,
  `id_referencia` INT NULL,
  `comentario` VARCHAR(120) NULL,
  `url` VARCHAR(120) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`)); */
  namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Han_Notifications extends Model
{
    protected  $table = "han_notifications";
 
    protected $fillable =[
    "id",
    "id_user",
    "estado",
    "id_referencia",
    "comentario",
    "url",
    "icon",
    "created_at",
    "updated_at"
];
}

?>