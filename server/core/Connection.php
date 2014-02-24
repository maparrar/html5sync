<?php
/** Connection File
* @package core @subpackage  */
/**
* Connection Class
*
* @author https://github.com/maparrar/maqinato
* @author maparrar <maparrar@gmail.com>
* @package core
* @subpackage 
*/
class Connection{
    /** 
     * Nombre de la conexión, puede ser: read, write, delete, all 
     * 
     * @var string
     */
    protected $name;
    /** 
     * Login de acceso a la base de datos para la conexión 
     * 
     * @var string
     */
    protected $login;
    /** 
     * Password de acceso a la base de datos para la conexión 
     * 
     * @var string
     */
    protected $password;
    /**
    * Constructor
    * @param string $name Nombre de la conexión, puede ser: read, write, delete, all        
    * @param string $login Login de acceso a la base de datos para la conexión        
    * @param string $password Password de acceso a la base de datos para la conexión        
    */
    function __construct($name="",$login="",$password=""){        
        $this->name=$name;
        $this->login=$login;
        $this->password=$password;
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   SETTERS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
    * Setter name
    * @param string $value Nombre de la conexión, puede ser: read, write, delete, all
    * @return void
    */
    public function setName($value) {
        $this->name=$value;
    }
    /**
    * Setter login
    * @param string $value Login de acceso a la base de datos para la conexión
    * @return void
    */
    public function setLogin($value) {
        $this->login=$value;
    }
    /**
    * Setter password
    * @param string $value Password de acceso a la base de datos para la conexión
    * @return void
    */
    public function setPassword($value) {
        $this->password=$value;
    }
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   SETTERS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
    /**
    * Getter: name
    * @return string
    */
    public function getName() {
        return $this->name;
    }
    /**
    * Getter: login
    * @return string
    */
    public function getLogin() {
        return $this->login;
    }
    /**
    * Getter: password
    * @return string
    */
    public function getPassword() {
        return $this->password;
    }    
    //>>>>>>>>>>>>>>>>>>>>>>>>>>>>   METHODS   <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<
}