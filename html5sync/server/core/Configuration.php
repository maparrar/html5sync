<?php
/** Configuration File
* @package html5sync @subpackage core */
/**
* Configuration Class
* Carga y administra los parámetros de configuración de la librería 
*
* @author https://github.com/maparrar/html5sync
* @author maparrar <maparrar@gmail.com>
* @package html5sync
* @subpackage core
*/
class Configuration{
    /** 
     * Parámetros de html5sync cargados desde el archivo de configuración
     * 
     * @var array
     */
    protected $parameters;
    /**
    * Constructor
    */
    function __construct($path="../config.php"){
        //Se establece timezone y carga la configuración
        $this->loadConfiguration($path);
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   SETTERS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   GETTERS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
        
    /**
     * Carga la configuración del archivo server/config.php
     */
    private function loadConfiguration($path){
        //Se leen las variables de configuración
        $this->parameters=require_once $path;
    }
    //**************************************************************************
    //>>>>>>>>>>>>>>>>>>>>>>>>   PUBLIC METHODS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    //**************************************************************************
    /**
    * Retorna el valor de uno de los parámetros de configuración
    * @param $name Nombre del parámetro que se quiere acceder
    * @param $subname Nombre del subíndice del parámetro que se quiere acceder
    * @return mixed Valor del parámetro
    */
    public function getParameter($parameter1,$parameter2=false,$parameter3=false) {
        $parameter=false;
        //Si existen los tres parámetros
        if(
                $parameter1&&array_key_exists($parameter1,$this->parameters)&&
                $parameter2&&array_key_exists($parameter2,$this->parameters[$parameter1])&&
                $parameter3&&array_key_exists($parameter3,$this->parameters[$parameter1][$parameter2])
            ){
            $parameter=$this->parameters[$parameter1][$parameter2][$parameter3];
        }elseif(
                $parameter1&&array_key_exists($parameter1,$this->parameters)&&
                $parameter2&&array_key_exists($parameter2,$this->parameters[$parameter1])
            ){
            $parameter=$this->parameters[$parameter1][$parameter2];
        }elseif(
                $parameter1&&array_key_exists($parameter1,$this->parameters)
            ){
            $parameter=$this->parameters[$parameter1];
        }
        return $parameter;
    }
}