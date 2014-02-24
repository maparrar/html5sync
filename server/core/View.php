<?php
/** View File
 * @package config */
//namespace maqinato\core;
/**
 * View Class
 * Clase que se aplica a las Vistas de las aplicación
 *
 * @author https://github.com/maparrar/maqinato
 * @author Alejandro Parra <maparrar@gmail.com> 
 * @package config
 */
class View{
    /**
     * Renderiza la vista pasada como parámetro, solo muestra la vista si la encuentra,
     * si no, muestra un error en el debug.
     * @param string $view Nombre de la vista a renderizar
     */
    public static function render($view){
        if(file_exists("engine/views/".$view.".php")){
            //Incluye los estilos básicos de maqinato
            Router::css("maqinato");
            require_once "engine/views/".$view.".php";
        }else{
            Maqinato::debug('View not found: '.$view);
        }
    }
    /**
     * Renderiza la página de error
     */
    public static function error(){
        self::render("error");
    }
}