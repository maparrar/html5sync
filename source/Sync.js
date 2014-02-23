/*
 * html5sync Plugin v.0.0.2 (https://github.com/maparrar/html5sync)
 * Feb 2014
 * - maparrar: http://maparrar.github.io
 * - jomejia: https://github.com/jomejia
 */
/**
 * Clase para la sincronización con el servidor [constructor pattern]
 * @param {object} params Objeto con los parámetros de la sincronización
 * @param {function} callback Función a la que se retornan los resultados
 */
var Sync = function(params,callback){
    /**************************************************************************/
    /******************************* ATTRIBUTES *******************************/
    /**************************************************************************/
    var self = this;
    self.onlineTimer;   //Cantidad de milisegundos en el que se verifica la conexión
    
    /**************************************************************************/
    /********************* CONFIGURATION AND CONSTRUCTOR **********************/
    /**************************************************************************/
    //Se mezclan los parámetros por defecto con los proporcionados por el usuario
    //y se agregan a la variable self (this del objeto)
    var def = {
        stateTimer: 5000,
        showState:false
    };
    self.params = $.extend(def, params);
    /**
     * Método privado que se ejecuta automáticamente. Hace las veces de constructor
     */
    var Sync = function() {
        self.state=false;
        $("body").prepend('<div id="html5sync_state"><div id="text">State: </div><div id="state">checking</div></div>');
        self.stateLabel=$("#html5sync_state");
        //Verifica el estado de la conexión
        checkState();
    }();
    
    /**************************************************************************/
    /**************************** PRIVATE METHODS *****************************/
    /**************************************************************************/
    /**
     * Verifica si la conexión está activa y actualiza el indicador de estado
     */
    function checkState(){
        setInterval(function(){
            if(navigator.onLine){
                self.state=true;
                if(self.params.showState){
                    self.stateLabel.removeClass("offline").addClass("online");
                    self.stateLabel.find("#state").text("On line");
                }
            }else{
                self.state=false;
                if(self.params.showState){
                    self.stateLabel.removeClass("online").addClass("offline");
                    self.stateLabel.find("#state").text("Off line");
                }
            }
        },self.params.stateTimer);
    };
    
    /**************************************************************************/
    /***************************** PUBLIC METHODS *****************************/
    /**************************************************************************/
    /**
     * Inserta objetos en un almacén. Recibe un objeto o un array de objetos
     * @param {string} storeName Nombre del almacén de datos donde se quiere insertar la información
     * @param {object[]} data Objeto o array de objetos
     * @param {function} callback Función a la que se retornan los resultados
     */
    self.publicFunction=function(storeName,data,callback){
        
    };
};