<?php
/** Request File
 * @core models @routing social */
/**
 * Request Class
 *
 * @author https://github.com/maparrar/maqinato
 * @author Alejandro Parra <maparrar@gmail.com>
 * @package core
 * @subpackage routing
 */
class Request{
    /** 
     * URI del request 
     * 
     * @var string
     */
    protected $uri;
    /** 
     * Controlador del request 
     * 
     * @var string
     */
    protected $controller;
    /** 
     * Función del request 
     * 
     * @var string
     */
    protected $function;
    /** 
     * Parámetros pasados al request 
     * 
     * @var array
     */
    protected $parameters;
    /**
     * Constructor de la clase
     * @param string $url Url del request, debe ser de la forma:
     *      /url_controller/url_function/url_paramater_1/.../url_parameter_n
     * @return void
     */
    function __construct($url=""){
        $this->uri=false;
        $this->controller=false;
        $this->function=false;
        $this->parameters=array();
        //Si la url no es vacía, se procesa
        if(trim($url)!=""){
            $this->uri=filter_var($url,FILTER_SANITIZE_URL);
            $requestArray=explode("/",$this->uri);
            $i=0;
            foreach ($requestArray as $value){
                if(trim($value)!=""){
                    if($i===0){
                        $this->controller=$value;
                    }elseif($i===1){
                        $this->function=$value;
                    }else{
                        $this->parameters[]=$value;
                    }
                    $i++;
                }
            }
        }
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   SETTERS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
    * Setter uri
    * @param string $value URI del request
    * @return void
    */
    public function setUri($value) {
        $this->uri=$value;
    }
    /**
    * Setter controller
    * @param string $value Controlador del request
    * @return void
    */
    public function setController($value) {
        $this->controller=$value;
    }        
    /**
    * Setter function
    * @param string $value Función del request
    * @return void
    */
    public function setFunction($value) {
        $this->function=$value;
    }        
    /**
    * Setter parameters
    * @param array $value Parámetros pasados al request
    * @return void
    */
    public function setParameters($value) {
        $this->parameters=$value;
    }        
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   SETTERS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
    * Getter: uri
    * @return string
    */
    public function getUri() {
        return $this->uri;
    }
    /**
    * Getter: controller
    * @return string
    */
    public function getController() {
        return $this->controller;
    }        
    /**
    * Getter: function
    * @return string
    */
    public function getFunction() {
        return $this->function;
    }        
    /**
    * Getter: parameters
    * @return array
    */
    public function getParameters() {
        return $this->parameters;
    }            
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   METHODS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
}