<?php
/** Maqinato File
 * @package core */
@session_start();
/**
 * Maqinato Class
 * Core class for Maqinato
 *
 * @author https://github.com/maparrar/maqinato
 * @author Alejandro Parra <maparrar@gmail.com> 
 * @package core
 * @todo Implementar el manejo de errores
 * @todo Usar el desarrollo orientado a tests
 * @todo Detección de tipo de servidor (development, testing, release, production)
 */
class Maqinato{
    /** Variable donde se almacena la configuración de maqinato
     * @var array
     */
    public static $config=array();
    /** Root folder of the application (i.e. /var/www/maqinato)
     * @var string
     */
    private static $root=null;
    /** Application name  (i.e. maqinato)
     * @var string
     */
    private static $application="";
    /** Variable de locale procesada por la aplicación
     * @var string
     */
    private static $locale="";
    /** Datos del servidor detectado por maqinato
     * @var string
     */
    private static $environment="";
    /** 
     * Objeto de tipo Request que almacena una estructura basada en la URL
     * @var Request Objeto de tipo Request
     */
    private static $request;
    /**
     * Objeto de tipo User que almacena el usuario actual de la sesión
     * @var User Objeto de tipo Usuario, si no hay registrado, el valor es false
     */
    private static $user;


    /**************************** DEBUG VARIABLES ****************************/
    /** Nivel de debug.
     *  0   No muestra ningún mensaje de maqinato
     *  1   Muestra la información básica y el request actual
     *  2   Muestra el nivel 1 + todos los datos de configuración de maqinato
     *  3   Muestra el nivel 2 + la lista de mensajes del debug
     */
    private static $debugLevel=3;
    /**
     * Array para almacenar todos los mensajes debug que se requieran
     */
    private static $debug=array();
    /** Timers to debug methods, procedures, functions or blok of codes
     * Each value of array must countain an array with:
     *  "name"=>"timer_name",
     *  "ini"=>"timer_start",
     *  "end"=>"timer_end",
     */
    private static $procTimers=array();

    /**
     * Ejecuta todos los procesos necesarios para cada request hecho por el cliente
     */
    public static function exec(){
        $ini=microtime(true);
        //Registra la función que carga las clases cuando no están include o require
        self::autoload();
        self::$root=$_SESSION["root"];
        self::$application=$_SESSION["application"];
        //Captura el request a partir de la URL actual
        self::$request=new Request(str_replace(self::$application."/","",filter_input(INPUT_SERVER,'REQUEST_URI',FILTER_SANITIZE_URL)));
        //Incluye los archivos de configuración
        self::$config=Router::loadConfig();
        //Procesa la configuración de i18n y l10n
        self::$locale=self::i18n();
        //Detecta el nombre del servidor y selecciona el ambiente
        self::$environment=self::loadEnvironment();
        //Carga el usuario de la sesión, si es posible
        $controller=new AccessController();
        if($controller->checkSession()){
            self::$user=$controller->getSessionUser();
        }else{
            self::$user=false;
        }
        //Calcula el tiempo que toma exec()
        $end=microtime(true);
        array_push(self::$procTimers,array("name"=>"maqinato","ini"=>$ini,"end"=>$end));
    }
    /**
     * Procesa la configuración de la internacionalización y localización
     */
    private static function i18n(){
        if(!self::$config["app"]["locale"]){
            $lang=filter_input(INPUT_SERVER,'HTTP_ACCEPT_LANGUAGE',FILTER_SANITIZE_STRING);
            $lang=substr($lang,0,strpos($lang,','));
            $lang=str_replace("-","_",$lang);
            $lang=reset(explode("_",$lang))."_".strtoupper(end(explode("_",$lang)));
        }else{
            $lang=self::$config["app"]["locale"];
        }
        //Verifica si el directorio con utf8 existe, sino busca el directorio estándar
        //sino busca el primero que contenga el idioma sin localización, por ejemplo
        //en "es_ES", si no lo encuentra, busca el primero que empiece con "es"
        $language=$lang;
        if(file_exists(self::$root."/locale/".$lang.".utf8")){
            $language=$lang.".utf8";
        }elseif(file_exists($lang)){
            $language=$lang;
        }else{
            $directory=self::$root."/locale";
            //Verifica los que empiecen con el idioma y toma el primero
            $list=glob($directory."/".reset(explode("_",$lang))."*");
            if(count($list)>0){
                if(file_exists($list[0].".utf8")){
                    $language=end(explode("/",$list[0])).".utf8";
                }else{
                    $language=end(explode("/",$list[0]));
                }
            }
        }
        //Configura las variables de i18n y l10n
        putenv("LC_ALL=$language");
        setlocale(LC_ALL,$language);
        // Set the text domain as 'messages'
        $domain='messages';
        bindtextdomain($domain,"./locale");
        bind_textdomain_codeset('default', 'UTF-8');
        textdomain($domain);
        return $language;
    }
    /**
     * Carga el environment a partir de la variable $_SERVER["SERVER_NAME"]
     * @return Environment El environment cargado
     */
    private static function loadEnvironment(){
        $environment=false;
        $serverName=filter_input(INPUT_SERVER,'SERVER_NAME',FILTER_SANITIZE_STRING);
        foreach(self::$config["environments"] as $envArray){
            $environment=new Environment();
            $environment->readEnvironment($envArray);
            if($environment->checkUrl($serverName)){
                break;
            }
        }
        return $environment;
    }
    
    /**************************************************************************/
    /*************************** GETTERS AND SETTERS **************************/
    /**************************************************************************/
    public static function application(){return self::$application;}
    public static function request(){return self::$request;}
    public static function root(){return self::$root;}
    public static function user(){return self::$user;}
    
    /**************************************************************************/
    /********************************* METHODS ********************************/
    /**************************************************************************/
    /**
     * Conecta con la base de datos definida para el Environment y retorna el 
     * handler.
     * @param string $connectionName Nombre la conexión a usar: read, write, delete, all
     * @return PDO Handler de la base de datos con la conexión especificada
     */
    public static function connect($connectionName="all"){
        return self::$environment->getDatabase()->connect($connectionName);
    }
    /**
     * Rutea el request de la URL
     */
    public static function route(){
        //Hace el routing del Request capturado
        Router::route(self::$request);
    }
    /**
     * Verifica que haya un usuario conectado
     * @return bool True si hay un usuario válido registrado, false en otro caso
     */
    public static function checkSession(){
        $status=false;
        if(self::$user&&get_class(self::$user)==="User"&&SecurityController::isEmail(self::$user->getEmail())){
            $status=true;
        }
        return $status;
    }
    /**
     * FUNCIONES ALIAS PARA ACCESO A ARCHIVOS LOCALES O ENSERVIDOR REMOTO
     */
    /** 
     * Retorna la ruta real de un archivo a partir de la ruta abstracta o relativa 
     * al folder de datos
     * @param string $abstractPath Ruta abstracta o relativa al folder de datos, i.e.: 
     *      - "users/richard.png"
     *      - "users/images/10.png"
     * @return mixed Retorna la ruta real de acuerdo al source elegido
     *      - "http://s3.amazonaws.com/foo/data/combinations/110.png?AWSAccessKeyId=AKIAJP2UTAR7UQEPY72Q&Expires=1356955251&Signature=WfmXKKXhBzjIU1ZsM4UO7F%2Bo3QM%3D"
     *      - "http://s3.amazonaws.com/foo/data/combinations/110.png"
     *      - "foo/data/combinations/110.png"
     */
    public static function dataUrl($abstractPath){
        return self::$environment->getFileServer()->dataUrl($abstractPath);
    }
    /**
     * Alias de dataUrl para ser usado como versión corta en la carga de imágenes
     * @param string $abstractPath Ruta abstracta de la imagen
     *      - "folder/110.png"
     * @return string Retorna la ruta de la imagen:
     *      - "http://s3.amazonaws.com/foo/data/combinations/110.png?AWSAccessKeyId=AKIAJP2UTAR7UQEPY72Q&Expires=1356955251&Signature=WfmXKKXhBzjIU1ZsM4UO7F%2Bo3QM%3D"
     *      - "http://s3.amazonaws.com/foo/data/combinations/110.png"
     *      - "/home/operator/foo/data/combinations/110.png"
     */
    public static function img($abstractPath){
        return self::$environment->getFileServer()->dataUrl($abstractPath);
    }
    /** 
     * Alias para dataPut, sube un archivo al sistema de archivos
     * @param string $source una ruta estándar donde está el archivo a guardar 
     *      - "/tmp/tempfile123.png"
     * @param string $abstractDestination Nombre de la ruta abstracta donde se guardará
     *      - "folder/saved.png"
     * @return bool:
     *      true if could save the file
     *      false otherwise
     */
    public function saveFile($source,$abstractDestination){
        return self::$environment->getFileServer()->saveFile($source,$abstractDestination);
    }
    /** 
     * Función utilizada para guardar una imagen que ha sido subida por medio de
     * un FILE input.
     * @param string $source Path and filename, i.e.: 
     *      - $_FILES["file"]["tmp_name"]
     * @param string $abstractDestination Name to save the file in the data folder:
     *      - "folder/110.png"
     * @return bool:
     *      true if could save the file
     *      false otherwise
     */
    public function saveUploadedFile($source,$abstractDestination){
        return self::$environment->getFileServer()->saveUploadedFile($source,$abstractDestination);
    }
    /** 
     * Copia un archivo de una ruta abstracta a otra ruta abstracta
     * @param string $abstractSource Ruta abstracta del archivo de origen
     *      - "foo/110.png"
     *      - "bar/images/10.png"
     * @param string $abstractDestination Ruta abstracta del archivo destino
     *      - "bar/110.png"
     *      - "users/foo/10.png"
     * @return bool True if successful
     */
    public function copyFile($abstractSource,$abstractDestination){
        return self::$environment->getFileServer()->copyFile($abstractSource,$abstractDestination);
    }
    /** 
     * Elimina un archivo del folder de datos a partir de su ruta abstracta
     * @param string $abstractPath Ruta abstracta del archivo que se eliminará 
     *      - "foo/110.png"
     *      - "users/bar/10.png"
     * @return bool True if successful
     */
    public function deleteFile($abstractPath){
        return self::$environment->getFileServer()->deleteFile($abstractPath);
    }
    /**
     * Verifica si un archivo existe, Si es externo, verifica la URL, sino verifica
     * con file_exist()
     * @abstract Debido a la demora en la comprobación si una imagen existe o no
     * se verifica si la imagen carga directamente en la etiqueta img y
     * se crea el directorio para imágenes por default que va con el
     * código de la aplicación. Esta función no se usa para archivos externos
     * en self::img() por ahora.
     * @param string $abstractPath Ruta abstracta del archivo a comprobar 
     */
    public function fileExist($abstractPath){
        return self::$environment->getFileServer()->fileExist($abstractPath);
    }
    
    /**************************************************************************/
    /******************************** SHORTCUTS *******************************/
    /**************************************************************************/
    /**
     * Función para acceder al directorio de rutas definido en la configuración.
     * Si se usa:
     *      Maqinato::directory("main");
     * es equivalente a usar:
     *      Maqinato::$config["paths"]["directory"]["main"];
     * @param string $address Dirección que se quiere buscar en el directorio
     * @return mixed Si se encuentra la dirección, se retorna la página a la que
     *              se debe redireccionar, sino, retorna false.
     */
    public static function directory($address){
        $response=false;
        if(array_key_exists($address,Maqinato::$config["paths"]["directory"])){
            $response=Maqinato::$config["paths"]["directory"][$address];
        }
        return $response;
    }

    /**************************************************************************/
    /********************************** UTILS *********************************/
    /**************************************************************************/
    /**
     * Función que carga automáticamente un archivo de una clase cuando no ha sido
     * cargado usando include o require. Esta función los carga con require.
     */
    private static function autoload(){
        $ini=microtime(true);
        spl_autoload_register(function($className){
            //Lista de directorios en los que se quiere buscar la clase
            $directories = array(
                self::$root."/engine/controllers",
                self::$root."/engine/models",
                self::$root."/core"
            );
            //Crea un iterador por cada directorio y busca las clases
            foreach ($directories as $directory){
                $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
                while($it->valid()){
                    //Si es un directorio de tipo . y no ..
                    if (!$it->isDot()){
                        //Obtiene el nombre del archivo
                        $fileName=end(explode(DIRECTORY_SEPARATOR,$it->getSubPathName()));
                        if($fileName==$className.'.php'){
                            require_once $it->key();
                            break;
                        }
                    }
                    $it->next();
                }
            }
        });
        $end=microtime(true);
        array_push(self::$procTimers,array("name"=>"autoload classes","ini"=>$ini,"end"=>$end));
    }
    /**
     * Agrega un mensaje de error al array de debug
     */
    public static function debug($message,$backtrace=false){
        if(self::$debugLevel>=3){
            if($backtrace){
                $vars=array_shift($backtrace);
            }else{
                $bt=debug_backtrace($options=DEBUG_BACKTRACE_IGNORE_ARGS);
                $vars=array_shift($bt);
            }
            self::$debug[]='
            <div class="mq_debug_msg mq_php">
                <div class="mq_title">
                    <div>PHP -></div>
                    <div class="mq_file">'.$vars["file"].'</div>
                    <div class="mq_line">[line: '.$vars["line"].']</div>
                    <div class="mq_time">'.date("Y-m-d H:i:s").'</div>
                </div>
                <div class="mq_content">
                    <div class="mq_code"></div>
                    <div class="mq_message">'.$message.'</div>
                </div>
            </div>';
        }
    }
    /**
     * Print the Maqinato information
     */
    public static function info(){
        $info=$config=$debug="";
        if(self::$debugLevel>0){
            if(self::$debugLevel>=1){
                $info='<div class="section">';
                    $info.='<div class="title">INFO</div>';
                    $info.='<ul>';
                        if(!self::$user){
                            $info.='<li>user: none</li>';
                        }else{
                            $info.='<li>user ['.self::$user->getId().']: '.self::$user->getEmail().'</li>';
                            $info.='<li>role: '.self::$user->getRole()->getName().'</li>';
                        }
                        $info.='<li>timers:</li>';    
                            $info.='<ul>';
                                foreach (self::$procTimers as $timer){
                                    $info.='<li>'.$timer["name"].": ".sprintf('%f',$timer["end"]-$timer["ini"])." ms</li>";
                                }
                            $info.='</ul>';
                        $info.='<li>root: '.self::$root.'</li>';
                        $info.='<li>application: '.self::$application.'</li>';
                        $info.='<li>environment:</li>';
                            $info.='<ul>';
                                $info.='<li>name: '.self::$environment->getName().'</li>';
                                $info.='<li>urls:</li>';
                                $info.=self::makeList(self::$environment->getUrls());
                                $info.='<li>database:</li>';
                                    $info.='<ul>';
                                        $info.='<li>name: '.self::$environment->getDatabase()->getName().'</li>';
                                        $info.='<li>driver: '.self::$environment->getDatabase()->getDriver().'</li>';
                                        $info.='<li>persistent: '.self::$environment->getDatabase()->getPersistent().'</li>';
                                        $info.='<li>host: '.self::$environment->getDatabase()->getHost().'</li>';
                                        $info.='<li>connections:</li>';
                                            $info.='<ul>';
                                                foreach(self::$environment->getDatabase()->getConnections() as $dbConnection){
                                                    $info.='<li>connection:</li>';
                                                    $info.='<ul>';
                                                        $info.='<li>name: '.$dbConnection->getName().'</li>';
                                                        $info.='<li>login: '.$dbConnection->getLogin().'</li>';
                                                        $info.='<li>password: '.$dbConnection->getPassword().'</li>';
                                                    $info.='</ul>';
                                                }
                                            $info.='</ul>';
                                    $info.='</ul>';
                                $info.='<li>fileServer:</li>';
                                    $info.='<ul>';
                                        $info.='<li>source: '.self::$environment->getFileServer()->getSource().'</li>';
                                        $info.='<li>isSSL: '.self::$environment->getFileServer()->getIsSSL().'</li>';
                                        $info.='<li>domain: '.self::$environment->getFileServer()->getDomain().'</li>';
                                        $info.='<li>bucket: '.self::$environment->getFileServer()->getBucket().'</li>';
                                        $info.='<li>folder: '.self::$environment->getFileServer()->getFolder().'</li>';
                                        $info.='<li>accessKey: '.self::$environment->getFileServer()->getAccessKey().'</li>';
                                        $info.='<li>secretKey: '.self::$environment->getFileServer()->getSecretKey().'</li>';
                                    $info.='</ul>';
                            $info.='</ul>';
                        $info.='<li>locale: '.self::$locale.'</li>';
                        $info.='<li>request:</li>';
                            $info.='<ul>';
                                $info.='<li>uri: '.self::$request->getUri().'</li>';
                                $info.='<li>controller: '.self::$request->getController().'</li>';
                                $info.='<li>function: '.self::$request->getFunction().'</li>';
                                $info.='<li>params:</li>';
                                    $info.='<ul>';
                                        foreach(self::$request->getParameters() as $key => $parameter){
                                            $info.='<li>'.$parameter.'</li>';
                                        }
                                    $info.='</ul>';
                            $info.='</ul>';
                    $info.='</ul>';
                $info.='</div>';
            }
            if(self::$debugLevel>=2){
                $config='<div class="section">';
                    $config.='<div class="title">CONFIG</div>';
                    $config.=self::makeList(self::$config);
                $config.='</div>';
            }
            if(self::$debugLevel>=3){
                $debug='<div class="section">';
                    $debug.='<div class="title">DEBUG</div>';
                    $debug.='<div id="mq_debug_msgs">';
                        foreach (self::$debug as $key => $message){
                            $debug.=$message;
                        }
                    $debug.='</div>';
                $debug.='</div>';
            }
            $output='<div class="maqinato_debug">';
            $output.='<div class=title>MAQINATO -> DEBUG LEVEL: '.self::$debugLevel.'</div>';
            $output.='<div class="column left">';
                $output.=$info;
                $output.=$config;
            $output.='</div>';
            $output.='<div class="column right">';
                $output.=$debug;
            $output.='</div>';
            $output.='</div>';
            echo $output;
        }
    }
    //Make a list from an array 
    private static function makeList($array){
        if(is_array($array)&&count($array)>0){
            $output='<ul>';
            foreach ($array as $key => $value){
                if(is_array($value)){
                    $output.='<li>['.$key.']: </li>';
                    $output.=self::makeList($value);
                }else{
                    $output.='<li>['.$key.']: '.$value.'</li>';
                }
            }
            $output.='</ul>'; 
        }
        return $output; 
    }
    /**
     * Write the main configuration variables in html to be readed from JS
     * @param array $parameters (opcional)Parámetros adicionales para incluir
     * @return string Write the variables in html
     */
    public static function configInHtml($parameters=false){
        $id=0;
        $name="";
        if(self::$user){
            $id=self::$user->getId();
            $name=self::$user->name();
        }else{
            $_SESSION["sessionLifetime"]=self::$config["client"]["sessionLifeTime"];
        }
        //Create the paths array to JS
        foreach (self::$config["paths"]["app"] as $key => $path) {
            $paths["$key"]=str_replace(self::root(),"",$path);
        }
        //Retorna el request
        $request["uri"]=self::$request->getUri();
        $request["controller"]=self::$request->getController();
        $request["function"]=self::$request->getFunction();
        $request["parameters"]=self::$request->getParameters();
        $data=array(
            "application"=>self::application(),
            "protocol"=>self::$config["app"]["protocol"],
            "user"=>$id,
            "userName"=>$name,
            "paths"=>$paths,
            "environment"=>self::$environment->getName(),
            "sessionLifetime"=>$_SESSION["sessionLifetime"],
            "sessionCheckTime"=>self::$config["client"]["sessionCheckTime"],
            "daemonsInterval"=>self::$config["client"]["daemonsInterval"],
            "request"=>$request,
            "parameters"=>$parameters
        );
        $html=
            "<!--Configuration data-->
            <input type='hidden' id='mq_config' 
                value='".json_encode($data)."'
            />";
        echo $html;
    }
}