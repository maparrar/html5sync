<?php
include_once '../core/Configuration.php';

//Carga la configuración del archivo server/config.php
$config=new Configuration();
//Se retorna la respuesta en JSON
$json='{'
    . '"id":0,'
    . '"database":"'.$config->getParameter("database","name").'",'
    . '"syncTimer":"'.$config->getParameter("main","browser","syncTimer").'",'
    . '"rowsPerPage":"'.$config->getParameter("main","rowsPerPage").'"'
.'}';
echo $json;