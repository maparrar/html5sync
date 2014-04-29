/*
 * html5sync Plugin v.0.0.3 (https://github.com/maparrar/html5sync)
 * Feb 2014
 * - maparrar: http://maparrar.github.io
 * - jomejia: https://github.com/jomejia
 */
/**
 * Clase para manejar la configuración de la librería
 * @param {object} params Objeto con los parámetros de la clase
 * @param {function} callback Función a la que se retornan los resultados
 */
var Configurator = function(params,callback){
    /**************************************************************************/
    /******************************* ATTRIBUTES *******************************/
    /**************************************************************************/
    var self = this;
    self.db;    //Configuration database
    
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
    var Configurator = function() {
        
    }();
    
    /**
     * Creates the html5sync database
     * @param {function} callback Función para retornar los resultados
     * @param {int} debugLevel Nivel inicial de debug para la función
     */
    function createDatabase(callback,debugLevel){
        self.showLoading(true);
        debug("Creating configuration database...","info",debugLevel+0);
        var stores=[{
                name:"Parameters",
                key:{keyPath:"id"},
                indexes:[{name:"id",key:"id",params:{unique:true}}]
            }
        ];
        var parameters={
            database: "html5sync",
            version: 1,                //Versión de la base de datos
            stores:stores,
            debugLevel:debugLevel+2
        };
        self.db=new Database(parameters,function(err){
            if(err){
                debug("Cannot create configuration database","bad",debugLevel+1);
                self.showLoading(false);
                callback(err);
            }else{
                debug("Configuration database created","good",debugLevel+0);
                self.showLoading(false);
                callback(false);
            }
        });
    }
    /**************************************************************************/
    /********************************** SETUP *********************************/
    /**************************************************************************/
    /**
     * Saves the configuration object in the html5sync database
     * @param {object} data Object with configuration data
     * @param {function} callback Función para retornar los resultados
     * @param {int} debugLevel Nivel inicial de debug para la función
     */
    self.saveConfiguration=function(data,callback,debugLevel){
        self.showLoading(true);
        if(!debugLevel)debugLevel=0;
        debug("Saving configuration in local database...","info",debugLevel+0);
        Database.databaseExists("html5sync",function(exists){
            if(exists){
                Database.loadDatabase("html5sync",function(err,configDatabase){
                    self.db=configDatabase;
                    if(err){
                        debug("Cannot load the configuration database","bad",debugLevel+1);
                        returnErrors();
                    }else{
                        addDataToDB(data);
                    }
                },debugLevel+1);
            }else{
                createDatabase(function(err){
                    if(err){
                        debug("Cannot create the configuration database","bad",debugLevel+1);
                        returnErrors();
                    }else{
                        addDataToDB(data);
                    }
                },debugLevel+1);
            }
            self.showLoading(false);
            function addDataToDB(data){
                //Verify the configuration data
                if(data.id===undefined||data.database===undefined||data.database.length===0){
                    returnErrors();
                }else{
                    self.db.update("Parameters",0,data,function(err){
                        if(err){
                            returnErrors();
                        }else{
                            debug("Configuration saved","good",debugLevel+0);
                            callback(false);
                        }
                    });
                }
            };
            function returnErrors(){
                debug("Cannot save the configuration in local database","bad",debugLevel+2);
                debug("Save configuration failed","bad",debugLevel+1);
                callback(new Error("Cannot write the configuration data in browser database"));
            };
        });
    };
    /**
     * Carga la configuración desde el servidor o desde la base de datos del 
     * navegador: BrowserDB.
     * Si no puede cargar desde el servidor, la carga de la base de datos, sino
     * retorna error.
     * @param {function} callback Función para retornar los resultados
     * @param {int} debugLevel Nivel inicial de debug para la función
     */
    self.loadConfiguration=function(callback,debugLevel){
        self.showLoading(true);
        if(!debugLevel)debugLevel=0;
        debug("Trying load configuration from local database...","info",debugLevel+0);
        Database.databaseExists("html5sync",function(exists){
            if(exists){
                debug("Configuration data found, loading...","good",debugLevel+1);
                Database.loadDatabase("html5sync",function(err,configDatabase){
                    if(err){
                        returnErrors();
                    }else{
                        configDatabase.get("Parameters",0,function(err,objects){
                            if(err||objects.length===0){
                                returnErrors();
                            }else{
                                var data=objects[0];
                                var databaseName=data.database;
                                if(databaseName===undefined||databaseName===""){
                                    returnErrors();
                                }else{
                                    debug("Configuration data loaded from local database","good",debugLevel+0);
                                    callback(false,data);
                                }
                            }
                        });
                    }
                },debugLevel+1);
            }else{
                returnErrors();
            }
            self.showLoading(false);
            function returnErrors(){
                debug("Inaccessible configuration data from server or browser database","bad",debugLevel+2);
                debug("Load configuration failed","bad",debugLevel+1);
                callback(new Error("Inaccessible configuration data from server or browser database"));
            };
        });
    };
};