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
        ajaxFolder:"server/ajax/",
        showLoading: false          //Function passed like parameter that shows the Loading gift.
    };
    self.params = $.extend(def, params);
    self.showLoading=self.params.showLoading;
    self.connector=self.params.connector;
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
        var stores=[
            {
                name:"Parameters",
                key:{keyPath:"id"},
                indexes:[{name:"id",key:"id",params:{unique:true}}]
            },
            {
                name:"Tables",
                key:{keyPath:"name"},
                indexes:[
                    {name:"name",key:"name",params:{unique:true}},
                    {name:"mode",key:"mode",params:{unique:false}},
                    {name:"columns",key:"columns",params:{unique:false}}
                ]
            },
            {
                name:"Transactions",
                key:{keyPath:"id",autoIncrement:true},
                indexes:[
                    {name:"id",key:"id",params:{unique:true}},
                    {name:"table",key:"table",params:{unique:false}},
                    {name:"key",key:"key",params:{unique:false}},
                    {name:"date",key:"date",params:{unique:false}},
                    {name:"transaction",key:"transaction",params:{unique:false}},
                    {name:"row",key:"row",params:{unique:false}}
                ]
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
                Database.loadDatabase({name:"html5sync",debugLevel:debugLevel+1},function(err,configDatabase){
                    self.db=configDatabase;
                    if(err){
                        debug("Cannot load the configuration database","bad",debugLevel+1);
                        returnErrors();
                    }else{
                        addDataToDB(data);
                    }
                });
            }else{
                createDatabase(function(err){
                    if(err){
                        debug("Cannot create the configuration database","bad",debugLevel+1);
                        returnErrors();
                    }else{
                        addDataToDB(data);
                    }
                });
            }
            self.showLoading(false);
            function addDataToDB(data){
                //Verify the configuration data
                if(data.id===undefined||data.database===undefined||data.database.length===0){
                    returnErrors();
                }else{
                    self.db.update("Parameters",false,0,data,function(err){
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
                Database.loadDatabase({name:"html5sync",debugLevel:debugLevel+1},function(err,configDatabase){
                    if(err){
                        returnErrors();
                    }else{
                        configDatabase.get("Parameters",false,0,function(err,objects){
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
                });
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
    /**
     * Saves the tables' structures
     * @param {object} tables List of Tables
     * @param {function} callback Función para retornar los resultados
     * @param {int} debugLevel Nivel inicial de debug para la función
     */
    self.saveTablesInConfiguration=function(tables,callback,debugLevel){
        self.showLoading(true);
        if(!debugLevel)debugLevel=0;
        debug("Saving tables in local database...","info",debugLevel+0);
        //Borra la estructura de las tablas anteriores de la base de datos de configuración
        self.db.clearStore("Tables",function(err){
            if(err){
                debug("Clear Tables in configuration fails","bad",debugLevel+1);
            }else{
                debug("Store 'Tables' successful empty","good",debugLevel+1);
                //Almacena la estructura de las tablas en la base de datos de configuración
                var list=new Array();
                for(var i in tables){
                    var table={
                        name:tables[i].name,
                        mode:tables[i].mode,
                        columns:tables[i].columns
                    };
                    //Se ordenan los campos de la tabla por el óden la la DB del servidor
                    table.columns.sort(function(a, b){
                        return a.order-b.order;
                    });
                    //Se convierten los valores booleanos
                    for(var j in table.columns){
                        var column=table.columns[j];
                        if(column.notNull==="1"){       column.notNull=true;        }else{   column.notNull=false;      }
                        if(column.autoIncrement==="1"){ column.autoIncrement=true;  }else{   column.autoIncrement=false;}
                        if(column.pk==="1"){            column.pk=true;             }else{   column.pk=false;           }
                        if(column.fk==="1"){            column.fk=true;             }else{   column.fk=false;           }
                        column.order=parseInt(column.order);
                    }
                    list.push(table);
                }
                debug("Saving list of tables in local database...","info",debugLevel+2);
                self.db.add("Tables",list,function(err){
                    if(err){
                        debug("Save Tables in configuration fails","bad",debugLevel+2);
                        callback(err);
                    }else{
                        debug("Save Tables in configuration success","good",debugLevel+2);
                        callback(false,list);
                    }
                });
            }
            self.showLoading(false);
        });
    };
    /**
     * Retorna la lista de estructuras de las tablas almacenadas en la base de datos de configuración
     * @param {function} callback Función para retornar los resultados
     * @param {int} debugLevel Nivel inicial de debug para la función
     */
    self.loadTablesFromConfiguration=function(callback,debugLevel){
        self.showLoading(true);
        if(!debugLevel)debugLevel=0;
        debug("Trying load tables structure from local database...","info",debugLevel+0);
        //Carga la lista de tablas de la base de datos de configuración
        self.db.list("Tables",function(err,tables){
            if(err){
                debug("Load Tables from configuration fails","bad",debugLevel+1);
                callback(err);
            }else{
                debug("Load Tables from configuration success","good",debugLevel+1);
                callback(false,tables);
            }
        });
    };
    
    /**************************************************************************/
    /****************************** TRANSACTIONS ******************************/
    /**************************************************************************/
    /**
     * Recibe una transacción realizada en el navegador.
     *  - Intenta enviarla por AJAX al servidor
     *      - Si se retorna mensaje de éxito, no hace nada más
     *      - Si el servidor no retorna éxito, almacena en BrowserDB
     *          - espera a la siguiente sincronización para intentar de nuevo
     * @param {object} transaction Transacción que se debe realizar
     * @param {function} callback Función que se ejecuta cuando se termina el proceso
     */
    self.execTransaction=function(transaction,callback){
        self.db.add("Transactions",transaction,function(err){
            if(err){
                if(callback)callback(err);
            }else{
                
                
                
                
                
                self.connector.storeTransactions(transaction,function(err,response){
                    if(err){
                        console.debug(err);
                    }else{
                        console.debug(response);
                    }
                });
                
                
                
                if(callback)callback(false);
                
                
                
//                if(self.params.debugCrud)debug("Add inserted to transactions success","good",self.params.debugLevel+3);
//                if(parseInt(counter)===parseInt(data.length)){
//                    if(callback)callback(false,indexes);
//                }
            }
        });
    };
};