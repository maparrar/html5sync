/*
 * html5sync Plugin v.0.0.3 (https://github.com/maparrar/html5sync)
 * Feb 2014
 * - maparrar: http://maparrar.github.io
 * - jomejia: https://github.com/jomejia
 */
/**
 * Clase para la sincronización con el servidor [constructor pattern]
 * @param {object} params Objeto con los parámetros de la sincronización
 * @param {function} callback Función a la que se retornan los resultados
 */
var Html5Sync = function(params,callback){
    /**************************************************************************/
    /******************************* ATTRIBUTES *******************************/
    /**************************************************************************/
    var self = this;
    self.configurator=false;    //Objeto para manejo de la configuración cargada del servidor
    self.config=false;          
    self.connector=false;       //Objeto de connexión con el servidor
    
    self.callback=callback; //Function to return responses
    
//    self.state;         //{bool} Estado de la conexión con el servidor.
    self.showLoadingCounter=0; //{int} Alamacena la cantidad de llamados a showLoading
    self.userId=false;  //Identificador del usuario
    self.databaseName=false;//Nombre de la base de datos
    self.database=false;//Base de datos del navegador
    /**************************************************************************/
    /********************* CONFIGURATION AND CONSTRUCTOR **********************/
    /**************************************************************************/
    //Se mezclan los parámetros por defecto con los proporcionados por el usuario
    //y se agregan a la variable self (this del objeto)
    var def = {
        debugging:false,
        debugLevel:0,       //Nivel de mensajes de debug que se muestran. 0 para mostrar todos
        html5syncFolder:"html5sync/",
        stateTimer: 10000,
        showState:false,
        viewer:false
    };
    self.params = $.extend(def, params);
    /**
     * Método privado que se ejecuta automáticamente. Hace las veces de constructor
     */
    var Html5Sync = function() {
        //Estructura el código HTML5
        setStructure();
        showLoading(true);
        
        //Initialize objects
        self.connector=new Connector({
            showLoading:showLoading
        });
        self.configurator=new Configurator();
        
        //Hace la carga de datos de configuración del servidor
        loadConfiguration(function(err,data){
            if(err){
                if(callback)callback(err);
            }else{
                self.config=data;
                self.userId=data.userId;  //Identificador del usuario
                self.databaseName=data.database;
                
                
                //Se prepara la base de datos
                prepareDatabase(function(err){
                    if(err){
                        if(callback)callback(err);
                    }else{
                        showLoading(false);
                        
                        //Cuando se cargue, inicia la sincronización
//                      sync()
                    }
                });
            }
        });
    }();
   
    /**************************************************************************/
    /****************************** INIT METHODS ******************************/
    /**************************************************************************/
    /**
     * Load the configuration from the server. If it is not found, load from the
     * local database. If not, throw an error.
     * @param {function} callback Funtion to return error if happens
     */
    function loadConfiguration(callback){
        debug("Starting the system setup...");
        self.connector.loadConfiguration(function(err,data){
            if(err){
                self.configurator.loadConfiguration(function(err,data){
                    if(err){
                        debug("Html5sync require Internet connection to setup the system","bad",1);
                        debug("Setup failed","bad");
                        if(callback)callback(err);
                    }else{
                        debug("Successful setup","good");
                        callback(false,data);
                    }
                },1);
            }else{
                self.configurator.saveConfiguration(data,function(err){
                    if(err){
                        debug("Html5sync require Internet connection to setup the system","bad",1);
                    }else{
                        debug("Successful setup","good");debug("");
                        callback(false,data);
                    }
                },1);
            }
        });
    };
    /**
     * Prepara la base de datos. Si está creada la carga, sino carga los datos 
     * desde el servidor para crearla. Si no puede cargar los datos, retorna el 
     * error.
     * @param {function} callback Funtion to return error if happens
     */
    function prepareDatabase(callback){
        debug("Preparing database "+returnDBName(),"info");
        Database.databaseExists(returnDBName(),function(exists){
//            exists=false;console.debug("Html5Sync.prepareDatabase: En pruebas, se desactiva exists <<<<<");
            if(exists){
                debug("Loading database "+returnDBName(),"info",1);
                Database.loadDatabase(returnDBName(),function(err,browserDatabase){
                    if(err){
                        debug("Cannot load the database "+returnDBName()+" trying delete...","bad",2);
                        Database.deleteDatabase(returnDBName(),function(err){
                            if(err){
                                debug("Cannot delete the database "+returnDBName()+" please clear the browser history and reload","bad",3);
                                if(callback)callback(err);
                            }else{
                                debug("Database "+returnDBName()+" deleted. Trying reload from server","bad",3);
                            }
                        });
                    }else{
                        self.database=browserDatabase;
                        debug("Database "+returnDBName()+" prepared","good");
                        debug("");
                        if(callback)callback(false);
                    }
                },1);
            }else{
                debug("Browser database "+returnDBName()+" not found","bad",1);
                debug("Trying reload database from server...","info",1);
                self.connector.reloadDatabase(returnDBName(),function(err,browserDatabase,tables){
                    if(err){
                        debug("Clear the browser history and reload","bad",1);
                        debug("Check the Internet connection to reload the database","bad");
                        if(callback)callback(err);
                    }else{
                        debug("Database loaded from server","good",1);
                        self.database=browserDatabase;
                        //Recarga todas las tablas antes de seguir
                        reloadTables(tables,function(err){
                            if(err){
                                if(callback)callback(err);
                            }else{
                                debug("All tables were successfuly loaded","good",1);
                                debug("Database "+returnDBName()+" prepared","good");
                                debug("");
                                if(callback)callback(false);
                            }
                        });
                    }
                },2);
            }
        });
    };
    
    /**
     * Recarga completamente las tablas de la base de datos
     * @param {array} tables Array de objetos tabla con la propiedad table.name
     * @param {function} callback Funtion to return error if happens
     */
    function reloadTables(tables,callback){
        debug("Reloading tables from server...","info",2);
        self.connector.setToBusy("reloadTables");
        for(var i in tables){
            var table=tables[i];
            debug("Loading table "+table.name+"...","info",3);
            //Se usa un callback en el callback para esperar que los datos
            //sean ingresados en BrowserDB antes de solicitar la siguiente página
            self.connector.reloadTable(table,function(err,page,finished,recallback){
                if(err){
                    debug("Cannot load table "+page.name,"bad",3);
                }else{
                    //Se insertan los registros en el almacen de datos
                    self.database.addPageToTable(page,function(err){
                        if(err){
                            debug("Cannot add the loaded page to the Object store: "+page.name,"bad",4);
                            if(callback)callback(err);
                        }else{
                            //Si se insertan con éxito, se llama el recallback para cargar la siguiente página
                            if(recallback)recallback(false);
                        }
                    });
                    //Si se acaban los datos de la tabla, se marca como finalizada
                    if(finished){
                        for(var j in tables){
                            if(tables[j].name===page.name){
                                tables[j].finished=true;
                            }
                        }
                        checkFinished();
                    }
                }
            });
        }
        //Verifica si se finalizaron todas las tablas
        function checkFinished(){
            var allFinished=true;
            for(var i in tables){
                if(!tables[i].finished){
                    allFinished=false;
                }
            }
            if(allFinished){
                self.connector.setToIdle("reloadTables");
                if(callback)callback(false);
            }
        }
    };
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /**************************************************************************/
    /****************************** SYNC METHODS ******************************/
    /**************************************************************************/
    /**
     * Inicia el proceso de sincronización.
     * @param {function} callback Función para retornar los resultados
     */
//    function startSync(callback){
////        showLoading(false);
//        sync();
//        setInterval(function(){
//            try{
//                sync(function(err){
//                    if(callback)callback(err);
//                });
//            }catch(e){
//                setState(false); 
//            }
//        },self.params.stateTimer);
//    };
    /**
     * Verifica si la conexión con el servidor está activa y actualiza el 
     * indicador de estado. Verifica si hay cambios en las tablas para el usuario,
     * si los hay, retorna los cambios.
     * @param {function} callback Función para retornar los resultados
     */
//    function sync(callback){
//        $.ajax({
//            url: self.params.html5syncFolder+"server/ajax/sync.php"
//        }).done(function(response) {
//            var data=JSON.parse(response);
//            if(data.error){
//                debug(data.error);
//                if(callback)callback(new Error(data.error));
//            }else{
//                self.userId=parseInt(data.userId);
//                self.databaseName=data.database;
//                var state=(data.state==="true")?true:false;
//                var changesInStructure=(data.changesInStructure==="true")?true:false;
//                var changesInData=(data.changesInData==="false")?false:data.changesInData;
//                //Marca como conectado
//                setState(state);
//                //Si hay cambios en la estructura o en los datos se deben recargar
//                if(changesInStructure){
//                    updateStructure();
//                }else{
//                    if(changesInData){
//                        for(var i in changesInData){
//                            updateTable(changesInData[i],function(err){
//                                if(err){
//                                    if(callback)callback(err);
//                                }
//                            });
//                        }
//                    }
//                }
//                //Si no existe la base de datos la crea y la carga por primera vez
//                var name=returnDBName();
//                databaseExists(name,function(exists){
//                    if(!exists){
//                        updateStructure();
//                    }else{
//                        if(!self.database){
//                            self.database=new Database({load:true,database:name},function(err){
//                                if(callback)callback(err);
//                            });
////                            buildViewer();
//                        }
//                    }
//                });
//                if(callback)callback(false);
//            }
//        }).fail(function(){
//            setState(false);
//        });
//    };
    /**
     * Carga de nuevo la estructura de la base de datos por medio de ajax
     * @param {function} callback Función para retornar los resultados
     */
//    function updateStructure(callback){
//        
//    };
    /**
     * Carga toda la información de las tablas
     * @param {function} callback Función para retornar los resultados
     */
//    function reloadData(callback){
//        if(!isBusy("reloadData")){
////            showLoading(true);
//            setToBusy("reloadData");
//            $.ajax({
//                url: self.params.html5syncFolder+"server/ajax/reloadData.php",
//                type: "POST"
//            }).done(function(response) {
//                var data=JSON.parse(response);
//                var tables=data.tables;
//                //Revisa si para cada tabla faltan datos, solicita los nuevos
//                for(var i in tables){
//                    var table=tables[i];
////                    showLoading(true);
//                    self.database.clearStore(table,function(err,table){
//                        if(!err){
//                            //Empieza a cargar los datos
//                            reloadTable(table);
//                        }
//                    });
//                }
//                if(callback)callback(false);
////                showLoading(false);
//                setToIdle("reloadData");
//            }).fail(function(){
//                if(callback)callback(new Error("Unable to reload data from the server"));
//                setState(false);
////                showLoading(false);
//                setToIdle("reloadData");
//            });
//        }
//    };
    /**
     * Verifica si se actualizaron todos los datos de una tabla, si faltan, carga la
     * siguiente página
     * @param {string} table Tabla proveniente del servidor en JSON
     * @param {function} callback Función para retornar los resultados
     */
//    function updateTable(table,callback){
//        var initialRow=parseInt(table.initialRow);
//        var numberOfRows=parseInt(table.numberOfRows);
//        var totalOfRows=parseInt(table.totalOfRows);
//        if((initialRow+numberOfRows)<totalOfRows){
//            $.ajax({
//                url: self.params.html5syncFolder+"server/ajax/updateTable.php",
//                data:{
//                    tableName:table.name,
//                    initialRow:initialRow+numberOfRows
//                },
//                type: "POST"
//            }).done(function(response) {
//                var data=JSON.parse(response);
//                var table=data.table;
//                debug(new Date().getTime()+"=><= Actualizando la tabla "+table.name+": "+(parseInt(table.initialRow)+1)+" de "+totalOfRows+" registros");
//                var rows=serverTableToJSON(table);
//                for(var j in rows){
//                    var row=rows[j];
//                    var pk=serverTableGetPK(table);
//                    //Si encuentra un campo que sea PK, actualiza el registro, sino lo inserta
//                    if(pk){
//                        self.database.update(table.name,pk.key,row,function(err){
//                            if(err){console.debug(err);}
//                        });
//                    }else{
//                        self.database.add(table.name,row,function(err){
//                            if(err){console.debug(err);}
//                        });
//                    }
//                }
//                //Si detecta que quedan datos por cargar de la tabla, los solicita
//                updateTable(table,function(err){
//                    if(callback)callback(err);
//                });
//                if(callback)callback(false);
//            }).fail(function(){
//                if(callback)callback(new Error("Unable to reload data from the server"));
//                setState(false);
//            });
//        }else{
////            showLoading(false);
//        }
//    };
    
    /**
     * Retorna la PK de una tabla recibida del servidor, si no tiene, retorna false
     * @param {string} table Tabla proveniente del servidor en JSON
     * @returns {mixed} False si no hay pk, objeto Field si la encuentra
     */
//    function serverTableGetPK(table){
//        var fields=table.fields;
//        var pk=false;
//        for(var i in fields){
//            if(fields[i].key==="PK"){
//                pk=fields[i];
//            }
//        }
//        return pk;
//    }
    
    
    /**************************************************************************/
    /***************************** OTHER METHODS ******************************/
    /**************************************************************************/
    /**
     * Establece el estado de la conexión con el servidor
     * @param {bool} state Estado de la conexión
     */
//    function setState(state){
//        self.state=true;
//        if(self.params.showState){
//            if(state){
//                self.stateLabel.removeClass("offline").addClass("online");
//                self.stateLabel.find("#state").text("on line");
//            }else{
//                self.stateLabel.removeClass("online").addClass("offline");
//                self.stateLabel.find("#state").text("off line");
//            }
//        }
//    };
    /**
     * Crea la estructura de la aplicación en HTML5. Define si se muestra el área
     * de debugging y/o de estado.
     */
    function setStructure(){
        //Create the exist() function for any selector. i.e: $("selector").exist()
        $.fn.exist=function(){return this.length>0;};
        //Starts without connection
        self.state=false;
        var state='';
        if(self.params.showState){
            state='<div id="html5sync_state">'+
                        '<div class="html5sync_spinner"></div>'+
                        '<div id="state">starting</div>'+
                    '</div>';
        }
        $("body").prepend(
                '<div id="html5sync_info">'+
                    state+
                '</div>'
            );
        self.stateLabel=$("#html5sync_state");
        self.loadingLabel=$(".html5sync_spinner");
        window.debug=function(message,type,level){
            if(level===undefined){
                level=0;
            }
            if(self.params.debugging){
                //Se verifica el nivel de debug
                var showable=false;
                if(self.params.debugLevel===0){
                    showable=true;
                }else{
                    if(level<=self.params.debugLevel){
                        showable=true;
                    }
                }
                if(showable){
                    //Si se pasa el nivel, se agregan los separadores de nivel
                    var levelText="";
                    if(level){
                        for(var i=0;i<level;i++){
                            levelText+="&#10140; ";
    //                        levelText+="&#8801; ";
                        }
                    }
                    //Especifica el tipo de mensaje
                    var typeText="";
                    if(type==='good'){
                        typeText="html5sync_message_good";
                    }else if(type==='bad'){
                        typeText="html5sync_message_bad";
                    }else if(type==='wait'){
                        typeText="html5sync_message_wait";
                    }else{
                        typeText="html5sync_message_info";
                    }
                    if(!$("#html5sync_debug").exist()){
                        $("body").prepend('<div id="html5sync_debug"></div>');
                    }
                    $("#html5sync_debug").append(levelText+'<span class="html5sync_message '+typeText+'">'+message+"</span><br>");
                    $("#html5sync_debug").scrollTop($('#html5sync_debug').get(0).scrollHeight);
                }
            }
        };
        //Agrega el visor de la base de datos
        if(self.params.viewer){
            $("body").append('<div id="html5sync_viewer"></div>');
        }
        showLoading(false);
    };
    /**
     * Muestra u oculta el loader de la librería
     * @param {boolean} state Estado en que se quiere poner la imagen del loader
     */
    function showLoading(state){
        if(state){
            self.loadingLabel.show();
            self.showLoadingCounter++;
        }else if(!state && self.showLoadingCounter>1){
            self.showLoadingCounter--;
        }else{
            self.loadingLabel.hide();
            self.showLoadingCounter=0;
        }
    };
    
    /**
     * Calcula el nombre de la base de datos indexedDB
     * @returns {string} Nombre de la base de datos
     */
    function returnDBName(){
        return "html5sync_"+self.databaseName+"_"+self.userId;
    }
    
    /**
     * Método para hacer debug sobre los datos de la base de datos indexedDB
     */
//    function printIndexedDB(){
//        
//    }
    
    /**
     * Función que muestra la base de datos actual en el HTML
     * @returns {undefined}
     */
//    function buildViewer(){
//        if(self.params.viewer){
//            var viewer=$("#html5sync_viewer");
//            console.debug(viewer);
//            console.debug(self.database);
//        }
//    }
//    function updateViewer(){
//        if(self.params.viewer){
//            var viewer=$("#html5sync_viewer");
//
//            console.debug(viewer);
//            console.debug(self.database);
//        }
//    }
    /**************************************************************************/
    /***************************** PUBLIC METHODS *****************************/
    /**************************************************************************/
    /**
     * Recarga los datos de las tablas permitidas. Toda la información de carga
     * está especificada en el archivo de configuración:
     * html5sync/server/config.php
     * @param {function} callback Función para retornar resultados
     */
//    self.forceReload=function(callback){
//        reloadData(function(err){
//            if(callback)callback(err);
//        });
//    };
};