<?php
/** Router File
 * @package config */
//namespace maqinato\core;
/**
 * Router Class
 * Specifies the paths and the application name for all the System.
 *
 * @author https://github.com/maparrar/maqinato
 * @author Alejandro Parra <maparrar@gmail.com> 
 * @package config
 */
class Router{
    /**
     * Retorna la ruta a partir de su nombre y del directorio de rutas definido
     * en config. Esta función retorna enlaces del tipo:<br>
     *      /maqinato/public/css/template.css<br>
     *      /maqinato/public/js/jquery/jquery-2.0.3.min.js<br>
     * que generalmente sirven para escribrir enlaces en el HTML como los de JS y
     * CSS. Esto fue necesario desde la inclusión del htaccess que redirecciona
     * todas las peticiones al index.php de la aplicación con mod_rewrite.<br>
     * Si el folder no existe, se muestra un error en el debug de Maqinato.
     */
    public static function path($folder){
        $path=false;
        if(file_exists(Maqinato::$config["paths"]["app"][$folder])||$folder==="root"){
            $path="/".Maqinato::application()."/".Maqinato::$config["paths"]["app"][$folder];
        }else{
            Maqinato::debug('Router::path("'.$folder.'") -> Folder not found',debug_backtrace());
        }
        return $path;
    }
    /**
     * Función para importar scripts de PHP en otros scripts. Se usa para centralizar
     * la forma de importar scritps, en caso de que se tengan que modificar los
     * paths, es decir, si se soluciona el problema que generó la función ::path().<br>
     * Si el archivo no existe, se muestra un error en el debug de Maqinato.
     */
    
    public static function import($filepath){
        $parts=pathinfo($filepath);
        $path=Maqinato::$config["paths"]["app"][$parts["dirname"]].$parts["basename"];
        if(file_exists($path)){
            require_once $path;
        }else{
            Maqinato::debug('require_once "'.$path.'" -> File not found.',debug_backtrace());
        }
    }
    /**
     * Procesa un Request y usa el controlador, la función y los parámetros para
     * redirigir a la página indicada.
     * @param Request $request Objeto de tipo Request que se routeará
     */
    public static function route(Request $request){
        if($request->getController()==""){
            self::redirect(Maqinato::directory("root"));
        }else{
            $address=Maqinato::directory($request->getController());
            if($address){
                View::render($address);
            }else{
                Maqinato::debug("Controller not detected");
                self::redirect("error/notFound");
            }
        }
    }
    /**
     * Redirecciona a una URL dentro de la aplicación. La url debe ser del tipo
     *      controller/function/parameter1/parameter2/...
     * @param string $url La url a la que se quiere redireccionar
     */
    public static function redirect($url){
        header( 'Location: /'.Maqinato::application().'/'.filter_var($url,FILTER_SANITIZE_URL));
    }
    /**
     * Return the html includes of a JS script or an array of scripts, if is not
     * registered, search the name in js folder
     * @param mixed Array or sigle name of JS scripts
     * @return string String with the includes for each JS provided
     */
    public static function js(){
        $string="";
        $values = func_get_args();
        if(array_search("basic",$values)!==false){
            array_splice($values,array_search("basic",$values),1,Maqinato::$config["paths"]["basic"]);
        }
        foreach ($values as $value){
            if(array_key_exists($value,Maqinato::$config["paths"]["js"])){
                $path=self::path("root").Maqinato::$config["paths"]["js"][$value];
                $string.='<script type="text/javascript" src="'.$path.'"></script>';
            }else{
                $ext=pathinfo($value,PATHINFO_EXTENSION);
                if(!$ext){
                    $value.=".js";
                }
                if(file_exists(self::path("js").$value)){
                    $string.='<script type="text/javascript" src="'.self::path("js").$value.'"></script>';
                }else{
                    Maqinato::debug('JS script NOT Found: '.$value,debug_backtrace());
                }
            }
        }
        echo $string;
    }
    /**
     * Return the html includes of a CSS script or an array of scripts, if is not
     * registered, search the name in css folder
     * @param mixed Array or sigle name of CSS scripts
     * @return string String with the includes for each CSS provided
     */
    public static function css(){
        $string="";
        $values = func_get_args();
        foreach ($values as $value){
            if(array_key_exists($value,Maqinato::$config["paths"]["css"])){
                $path=self::path("root").Maqinato::$config["paths"]["css"][$value];
                $string.='<link rel="stylesheet" type="text/css" href="'.$path.'">';
            }else{
                $ext=pathinfo($value,PATHINFO_EXTENSION);
                if(!$ext){
                    $value.=".css";
                }
                if(file_exists(Maqinato::$config["paths"]["app"]["css"].$value)){
                    $string.='<link rel="stylesheet" type="text/css" href="'.self::path("css").$value.'">';
                }else{
                    Maqinato::debug('CSS script NOT Found: '.$value,debug_backtrace());
                }
            }
        }
        echo $string;
    }
    /**
     * Carga los archivos de configuración en las variables de configuraión
     * @return void
     */
    public static function loadConfig(){
        return array(
            "app"           =>  require_once Maqinato::root().'/engine/config/app.php',
            "environments"  =>  require_once Maqinato::root().'/engine/config/environment.php',
            "client"        =>  require_once Maqinato::root().'/engine/config/client.php',
            "paths"         =>  require_once Maqinato::root().'/engine/config/paths.php'
        );
    }
}
?>
