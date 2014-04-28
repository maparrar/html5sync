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
    
    
    /**************************************************************************/
    /********************* CONFIGURATION AND CONSTRUCTOR **********************/
    /**************************************************************************/
    //Se mezclan los parámetros por defecto con los proporcionados por el usuario
    //y se agregan a la variable self (this del objeto)
    var def = {
        ajaxFolder:"html5sync/server/ajax/",
        showLoading: false          //Function passed like parameter that shows the Loading gift.
    };
    self.params = $.extend(def, params);
    self.showLoading=self.params.showLoading;
    /**
     * Método privado que se ejecuta automáticamente. Hace las veces de constructor
     */
    var Connector = function() {
        
    }();
    /**************************************************************************/
    /****************************** SETUP METHODS *****************************/
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
            url: self.params.ajaxFolder+"loadConfiguration.php",
            type: "POST"
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
    /**************************************************************************/
    /******************************* SYNC METHODS *****************************/
    /**************************************************************************/
    /**
     * Recarga la base de datos del navegador (BrowserDB) y la reemplaza. Luego de
     * usar esta función es necesario recargar todos los datos antes de usar la 
     * información
     * @param {string} databaseName Nombre de la database BrowserDB
     * @param {function} callback Función para retornar los resultados
     * @param {int} debugLevel Nivel inicial de debug para la función
     */
    self.reloadDatabase=function(databaseName,callback,debugLevel){
        if(!debugLevel)debugLevel=0;
        self.showLoading(true);
        if(!isBusy("reloadDatabase")){
            setToBusy("reloadDatabase");
            debug("Reloading database from server...","info",debugLevel);
            debug("THIS MAY TAKE A WHILE","wait",debugLevel);
            $.ajax({
                url: self.params.ajaxFolder+"reloadDatabase.php",
                type: "POST"
            }).done(function(response) {
                var data=JSON.parse(response);
                if(data.error){
                    console.debug("error");
                    debug("SERVER: "+data.error,"bad",debugLevel+1);
                    if(callback)callback(new Error("SERVER: "+data.error));
                }else{
                    //Configura los parámetros para crear la base de datos
                    var parameters={
                        database: databaseName,
                        version: parseInt(data.version),                //Versión de la base de datos
                        stores:Database.tablesToStores(data.tables),
                        debugLevel:debugLevel+1,
                        debugCrud:true
                    };
                    var database=new Database(parameters,function(err){
                        if(err){
                            debug("Cannot create the database "+databaseName,"bad",debugLevel);
                            if(callback)callback(err);
                        }else{
                            debug("Database "+databaseName+" created","good",debugLevel);
                            //Si todo está bien, retorna la base de datos creada
                            if(callback)callback(false,database,data.tables);
                        }
                    });
                }
                setToIdle("reloadDatabase");
            }).fail(function(){
                debug("Cannot reload database from server","bad",debugLevel);
                callback(new Error("Cannot reload database from server"));
                setToIdle("reloadDatabase");
                self.showLoading(false);
            });
        }
    };
    
    /**
     * Verifica si se cargaron todos los datos de una tabla, si faltan, carga la
     * siguiente página
     * @param {object} table Tabla proveniente del servidor en JSON
     * @param {function} callback Función para retornar los resultados
     */
    self.reloadTable=function(table,callback){
        
        
        callback(false,true);
        
        //        var initialRow=parseInt(table.initialRow);
//        var numberOfRows=parseInt(table.numberOfRows);
//        var totalOfRows=parseInt(table.totalOfRows);
//        if((initialRow+numberOfRows)<totalOfRows){
//            setToBusy("reloadData");
//            $.ajax({
//                url: self.params.html5syncFolder+"server/ajax/reloadTable.php",
//                data:{
//                    tableName:table.name,
//                    initialRow:initialRow+numberOfRows
//                },
//                type: "POST"
//            }).done(function(response) {
//                var data=JSON.parse(response);
//                var table=data.table;
//                debug(new Date().getTime()+"=><= Recargando la tabla "+table.name+": "+(parseInt(table.initialRow)+1)+" de "+totalOfRows+" registros");
//                //Guarda los datos en la base de datos del navegdor
//                fillTable(table,function(err){
//                    if(err){
//                        if(callback)callback(err);
//                    }else{
//                        //Si detecta que quedan datos por cargar de la tabla, los solicita
//                        reloadTable(table);
//                    }
//                });
//                if(callback)callback(false);
//            }).fail(function(){
//                if(callback)callback(new Error("Unable to reload data from the server"));
//                setState(false);
//            });
//        }else{
////            showLoading(false);
//        }
//        //Si es la última tabla cargada, libera la función de recarga de datos
//        if((totalOfRows-(initialRow+1))<=numberOfRows){
//            setToIdle("reloadData");
//        }
    };
    
    /**************************************************************************/
    /***************************** STATUS METHODS *****************************/
    /**************************************************************************/
    /**
     * Verifica si una función que no admite repeticiones está ocupada
     * @param {string} name Nombre de la función
     * @returns {bool} True si la función está ocupada, false en otro caso
     */
    function isBusy(name){
        var busy=false;
        for(var i in self.busyFunctions){
            if(self.busyFunctions[i]===name){
                busy=true;
            }
        }
        return busy;
    };
    /**
     * Agrega el nombre de una función al array de funciones ocupadas que no se
     * pueden usar hasta que no se desocupen
     * @param {string} name Nombre de la función
     */
    function setToBusy(name){
        if(!isBusy(name)){
            self.busyFunctions.push(name);
        }
    };
    /**
     * Elimina el nombre de una función del array de funciones ocupadas que no se
     * pueden usar hasta que no se desocupen.
     * @param {string} name Nombre de la función
     */
    function setToIdle(name){
        for(var i in self.busyFunctions){
            if(self.busyFunctions[i]===name){
                self.busyFunctions.splice(i,1);
            }
        }
    };
};