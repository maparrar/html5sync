/*
 * html5sync Plugin v.0.0.3 (https://github.com/maparrar/html5sync)
 * Feb 2014
 * - maparrar: http://maparrar.github.io
 * - jomejia: https://github.com/jomejia
 */
/**
 * Clase para manejar las conexiones con el servidor y la transferencia de información
 * @param {object} params Objeto con los parámetros de la clase
 * @param {function} callback Función a la que se retornan los resultados
 */
var Connector = function(params,callback){
    /**************************************************************************/
    /******************************* ATTRIBUTES *******************************/
    /**************************************************************************/
    var self = this;
    self.busyFunctions=new Array();  //Lista de funciones ocupadas que solo se pueden usar una vez al tiempo
    
    self.state;         //{bool} Estado de la conexión con el servidor.
    self.showLoadingCounter=0; //{int} Almacena la cantidad de llamados a showLoading
    self.userId=false;  //Identificador del usuario
    /**************************************************************************/
    /********************* CONFIGURATION AND CONSTRUCTOR **********************/
    /**************************************************************************/
    //Se mezclan los parámetros por defecto con los proporcionados por el usuario
    //y se agregan a la variable self (this del objeto)
    var def = {
        debugging:false,
        ajaxFolder:"html5sync/server/ajax/",
        stateTimer: 10000,
        showState:false,
        viewer:false
    };
    self.params = $.extend(def, params);
    /**
     * Método privado que se ejecuta automáticamente. Hace las veces de constructor
     */
    var Connector = function() {
        //Estructura el código HTML5
        
        
        //Hace la carga de datos de configuración del servidor
        
        //Almacena la configuración en indexedDB
        
        //Si no encuentra la configuración, recarga la configuración del servidor
        
        //Si no encuentra y no puede cargar del servidor, avisa y se inactiva
        
        
        
    }();
    /**************************************************************************/
    /********************************** SETUP *********************************/
    /**************************************************************************/
    /**
     * Carga la configuración desde el servidor o desde la base de datos del 
     * navegador: BrowserDB.
     * Si no puede cargar desde el servidor, la carga de la base de datos, sino
     * retorna error.
     * @param {function} callback Función para retornar los resultados
     * @param {int} debugLevel Nivel inicial de debug para la función
     */
    self.loadConfiguration=function(callback,debugLevel){
        if(!debugLevel)debugLevel=0;
        debug("Loading configuration from server...","info",debugLevel+1);
        $.ajax({
            url: self.params.ajaxFolder+"loadConfiguration.php"
        }).done(function(response){
            try{
                var data=JSON.parse(response);
                if(data.database===undefined||data.database===""){
                    returnErrors();
                }else{
                    debug("Configuration data loaded from server","good",debugLevel+1);
                    callback(false,data);
                }
            }catch(err){
                returnErrors();
            }
        }).fail(function(){
            returnErrors();
        });
        function returnErrors(){
            debug("Load configuration from server failed","bad",debugLevel+1);
            callback(new Error("Load configuration from server failed"));
        }
    };
};