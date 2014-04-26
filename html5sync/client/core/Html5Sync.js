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
    self.conn=false;    //Objeto de connexión con el servidor
    
    
    
    
    
    self.busyFunctions=new Array();  //Lista de funciones ocupadas que solo se pueden usar una vez al tiempo
    
    self.state;         //{bool} Estado de la conexión con el servidor.
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
        
        
        
        //Hace la carga de datos de configuración del servidor
        self.conn=new Connection();
        self.conn.loadConfiguration(function(err,configuration){
            if(err){
                if(callback)callback(err);
            }else{
                debug(configuration);
            }
        });
        
        
        
        
        
        
        
        //Almacena la configuración en indexedDB
        
        //Si no encuentra la configuración, recarga la configuración del servidor
        
        //Si no encuentra y no puede cargar del servidor, avisa y se inactiva
        
        
        
        //Inicia el proceso de sincronización
//        startSync();
    }();
   
    /**************************************************************************/
    /****************************** SYNC METHODS ******************************/
    /**************************************************************************/
    /**
     * Inicia el proceso de sincronización.
     * @param {function} callback Función para retornar los resultados
     */
    function startSync(callback){
        showLoading(false);
        sync();
        setInterval(function(){
            try{
                sync(function(err){
                    if(callback)callback(err);
                });
            }catch(e){
                setState(false); 
            }
        },self.params.stateTimer);
    };
    /**
     * Verifica si la conexión con el servidor está activa y actualiza el 
     * indicador de estado. Verifica si hay cambios en las tablas para el usuario,
     * si los hay, retorna los cambios.
     * @param {function} callback Función para retornar los resultados
     */
    function sync(callback){
        $.ajax({
            url: self.params.html5syncFolder+"server/ajax/sync.php"
        }).done(function(response) {
            var data=JSON.parse(response);
            if(data.error){
                debug(data.error);
                if(callback)callback(new Error(data.error));
            }else{
                self.userId=parseInt(data.userId);
                self.databaseName=data.database;
                var state=(data.state==="true")?true:false;
                var changesInStructure=(data.changesInStructure==="true")?true:false;
                var changesInData=(data.changesInData==="false")?false:data.changesInData;
                //Marca como conectado
                setState(state);
                //Si hay cambios en la estructura o en los datos se deben recargar
                if(changesInStructure){
                    updateStructure();
                }else{
                    if(changesInData){
                        for(var i in changesInData){
                            updateTable(changesInData[i],function(err){
                                if(err){
                                    if(callback)callback(err);
                                }
                            });
                        }
                    }
                }
                //Si no existe la base de datos la crea y la carga por primera vez
                var name=returnDBName();
                databaseExists(name,function(exists){
                    if(!exists){
                        updateStructure();
                    }else{
                        if(!self.database){
                            self.database=new Database({load:true,database:name},function(err){
                                if(callback)callback(err);
                            });
                            buildViewer();
                        }
                    }
                });
                if(callback)callback(false);
            }
        }).fail(function(){
            setState(false);
        });
    };
    /**
     * Carga de nuevo la estructura de la base de datos por medio de ajax
     * @param {function} callback Función para retornar los resultados
     */
    function updateStructure(callback){
        if(!isBusy("updateStructure")){
            showLoading(true);
            setToBusy("updateStructure");
            debug("..::DB INFO::.. Se detectaron cambios en la estructura de las tablas de la BD. Actualizando...");
            $.ajax({
                url: self.params.html5syncFolder+"server/ajax/updateStructure.php",
                type: "POST"
            }).done(function(response) {
                var data=JSON.parse(response);
                self.userId=parseInt(data.userId);
                self.databaseName=data.database;
                var version=parseInt(data.version);
                var tables=data.tables;
                var parameters=parseDatabaseParameters(version,tables);
                self.database=new Database(parameters,function(err){
                    if(err){
                        if(callback)callback(err);
                    }else{
                        if(callback)callback(false);
                        //Carga todos los datos de las tablas
                        reloadData(function(err){
                            if(err){
                                if(callback)callback(err);
                            }
                        });
                    }
                });
                showLoading(false);
                setToIdle("updateStructure");
            }).fail(function(){
                callback(new Error("Unable to connect the server to update structure"));
                setState(false);
                showLoading(false);
                setToIdle("updateStructure");
            });
        }
    };
    /**
     * Carga toda la información de las tablas
     * @param {function} callback Función para retornar los resultados
     */
    function reloadData(callback){
        if(!isBusy("reloadData")){
            showLoading(true);
            setToBusy("reloadData");
            $.ajax({
                url: self.params.html5syncFolder+"server/ajax/reloadData.php",
                type: "POST"
            }).done(function(response) {
                var data=JSON.parse(response);
                var tables=data.tables;
                //Revisa si para cada tabla faltan datos, solicita los nuevos
                for(var i in tables){
                    var table=tables[i];
                    showLoading(true);
                    self.database.clearStore(table,function(err,table){
                        if(!err){
                            //Empieza a cargar los datos
                            reloadTable(table);
                        }
                    });
                }
                if(callback)callback(false);
                showLoading(false);
                setToIdle("reloadData");
            }).fail(function(){
                if(callback)callback(new Error("Unable to reload data from the server"));
                setState(false);
                showLoading(false);
                setToIdle("reloadData");
            });
        }
    };
    /**
     * Verifica si se cargaron todos los datos de una tabla, si faltan, carga la
     * siguiente página
     * @param {string} table Tabla proveniente del servidor en JSON
     * @param {function} callback Función para retornar los resultados
     */
    function reloadTable(table,callback){
        var initialRow=parseInt(table.initialRow);
        var numberOfRows=parseInt(table.numberOfRows);
        var totalOfRows=parseInt(table.totalOfRows);
        if((initialRow+numberOfRows)<totalOfRows){
            setToBusy("reloadData");
            $.ajax({
                url: self.params.html5syncFolder+"server/ajax/reloadTable.php",
                data:{
                    tableName:table.name,
                    initialRow:initialRow+numberOfRows
                },
                type: "POST"
            }).done(function(response) {
                var data=JSON.parse(response);
                var table=data.table;
                debug(new Date().getTime()+"=><= Recargando la tabla "+table.name+": "+(parseInt(table.initialRow)+1)+" de "+totalOfRows+" registros");
                //Guarda los datos en la base de datos del navegdor
                fillTable(table,function(err){
                    if(err){
                        if(callback)callback(err);
                    }else{
                        //Si detecta que quedan datos por cargar de la tabla, los solicita
                        reloadTable(table);
                    }
                });
                if(callback)callback(false);
            }).fail(function(){
                if(callback)callback(new Error("Unable to reload data from the server"));
                setState(false);
            });
        }else{
            showLoading(false);
        }
        //Si es la última tabla cargada, libera la función de recarga de datos
        if((totalOfRows-(initialRow+1))<=numberOfRows){
            setToIdle("reloadData");
        }
    };
    /**
     * Verifica si se actualizaron todos los datos de una tabla, si faltan, carga la
     * siguiente página
     * @param {string} table Tabla proveniente del servidor en JSON
     * @param {function} callback Función para retornar los resultados
     */
    function updateTable(table,callback){
        var initialRow=parseInt(table.initialRow);
        var numberOfRows=parseInt(table.numberOfRows);
        var totalOfRows=parseInt(table.totalOfRows);
        if((initialRow+numberOfRows)<totalOfRows){
            $.ajax({
                url: self.params.html5syncFolder+"server/ajax/updateTable.php",
                data:{
                    tableName:table.name,
                    initialRow:initialRow+numberOfRows
                },
                type: "POST"
            }).done(function(response) {
                var data=JSON.parse(response);
                var table=data.table;
                debug(new Date().getTime()+"=><= Actualizando la tabla "+table.name+": "+(parseInt(table.initialRow)+1)+" de "+totalOfRows+" registros");
                var rows=serverTableToJSON(table);
                for(var j in rows){
                    var row=rows[j];
                    var pk=serverTableGetPK(table);
                    //Si encuentra un campo que sea PK, actualiza el registro, sino lo inserta
                    if(pk){
                        self.database.update(table.name,pk.key,row,function(err){
                            if(err){console.debug(err);}
                        });
                    }else{
                        self.database.add(table.name,row,function(err){
                            if(err){console.debug(err);}
                        });
                    }
                }
                //Si detecta que quedan datos por cargar de la tabla, los solicita
                updateTable(table,function(err){
                    if(callback)callback(err);
                });
                if(callback)callback(false);
            }).fail(function(){
                if(callback)callback(new Error("Unable to reload data from the server"));
                setState(false);
            });
        }else{
            showLoading(false);
        }
    };
    /**
     * Recibe una tabla formateada del servidor, la formatea para el navegador y agrega
     * los registros
     * @param {string} table Tabla proveniente del servidor en JSON
     * @param {function} callback Función para retornar los resultados
     */
    function fillTable(table,callback){
        var rows=serverTableToJSON(table);
        self.database.add(table.name,rows,function(err){
            if(err){
                if(callback)callback(err);
            }else{
                if(callback)callback(false);
            }
        });
    };
    /**
     * Recibe una tabla del servidor y la modifica para que se puedan ingresar 
     * en la base de datos del navegador. Asocia a cada dato de cada registro 
     * con el nombre de la columna. Retorna un JSON bien formado
     * @param {string} table Tabla proveniente del servidor en JSON
     * @returns {object} La tabla en JSON bien formada
     */
    function serverTableToJSON(table){
        var rows=table.data;
        var fields=table.fields;
        var registers=new Array();
        for(var i in rows){
            var row=rows[i];
            var register=new Object();
            for(var j in row){
                register[fields[j].name]=row[j];
            }
            registers.push(register);
        }
        return registers;
    }
    /**
     * Retorna la PK de una tabla recibida del servidor, si no tiene, retorna false
     * @param {string} table Tabla proveniente del servidor en JSON
     * @returns {mixed} False si no hay pk, objeto Field si la encuentra
     */
    function serverTableGetPK(table){
        var fields=table.fields;
        var pk=false;
        for(var i in fields){
            if(fields[i].key==="PK"){
                pk=fields[i];
            }
        }
        return pk;
    }
    
    
    /**************************************************************************/
    /***************************** OTHER METHODS ******************************/
    /**************************************************************************/
    /**
     * Establece el estado de la conexión con el servidor
     * @param {bool} state Estado de la conexión
     */
    function setState(state){
        self.state=true;
        if(self.params.showState){
            if(state){
                self.stateLabel.removeClass("offline").addClass("online");
                self.stateLabel.find("#state").text("on line");
            }else{
                self.stateLabel.removeClass("online").addClass("offline");
                self.stateLabel.find("#state").text("off line");
            }
        }
    };
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
                        '<div id="state">checking</div>'+
                        
                    '</div>';
        }
        $("body").prepend(
                '<div id="html5sync_info">'+
                    state+
                '</div>'
            );
        self.stateLabel=$("#html5sync_state");
        self.loadingLabel=$(".html5sync_spinner");
        window.debug=function(message,level){
            if(self.params.debugging){
                //Si se pasa el nivel, se agregan los separadores de nivel
                var levelText="";
                if(level){
                    for(var i=0;i<parseInt(level);i++){
                        levelText+="&#10148; ";
                    }
                }
                if(!$("#html5sync_debug").exist()){
                    $("body").prepend('<div id="html5sync_debug"></div>');
                }
                $("#html5sync_debug").append(levelText+message+"<br>");
                $("#html5sync_debug").scrollTop($('#html5sync_debug').get(0).scrollHeight);
            }
        };
        //Agrega el visor de la base de datos
        if(self.params.viewer){
            $("body").append('<div id="html5sync_viewer"></div>');
        }
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
     * Crea la parametrización de la base de datos a partir de los datos obtenidos
     * del servidor
     * @param {int} version Número de versión
     * @param {array} tables Lista de tablas a convertir
     * @returns {array} Lista de parámetros para pasar a la base de datos
     */
    function parseDatabaseParameters(version,tables){
        var stores=new Array();
        for(var i in tables){
            stores.push(tableToStore(tables[i]));
        }
        //Lista de parámetros que define la configuración de la base de datos
        return {
            database: returnDBName(),
            version: version,                //Versión de la base de datos
            stores:stores     
        };
    };
    /**
     * Función que convierte una tabla en formato JSON a un almacén de objetos
     * @param {string} table Tabla en JSON
     * @returns {string} Almacén de objetos en JSON
     */
    function tableToStore(table){
        var indexes=new Array();
        var key={autoIncrement : true};
        for(var i in table.fields){
            var field=table.fields[i];
            var unique=false;
            if(field.key==="PK"){
                unique=true;
                key={keyPath:field.name};
            }
            var index={
                name:field.name,
                key:field.name,
                params:{unique: unique}
            };
            indexes.push(index);
        }
        var store={
            name:table.name,
            key:key,
            indexes:indexes
        };
        return store;
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
    function printIndexedDB(){
        
    }
    /**
     * Check if a database exists
     * @param {string} name Database name
     * @param {function} callback Function to return the response
     * @returns {bool} True if the database exists
     */
    function databaseExists(name,callback){
        var dbExists = true;
        var request = window.indexedDB.open(name);
        request.onupgradeneeded = function (e){
            if(request.result.version===1){
                dbExists = false;
                window.indexedDB.deleteDatabase(name);
                if(callback)
                    callback(dbExists);
            }
            
        };
        request.onsuccess = function(e) {
            if(dbExists){
                if(callback)
                        callback(dbExists);
            }
        };
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
    /**
     * Verifica si una función está ocupada
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
    }
    /**
     * Función que muestra la base de datos actual en el HTML
     * @returns {undefined}
     */
    function buildViewer(){
        if(self.params.viewer){
            var viewer=$("#html5sync_viewer");

            console.debug(viewer);
            console.debug(self.database);
        }
    }
    function updateViewer(){
        if(self.params.viewer){
            var viewer=$("#html5sync_viewer");

            console.debug(viewer);
            console.debug(self.database);
        }
    }
    /**************************************************************************/
    /***************************** PUBLIC METHODS *****************************/
    /**************************************************************************/
    /**
     * Recarga los datos de las tablas permitidas. Toda la información de carga
     * está especificada en el archivo de configuración:
     * html5sync/server/config.php
     * @param {function} callback Función para retornar resultados
     */
    self.forceReload=function(callback){
        reloadData(function(err){
            if(callback)callback(err);
        });
    };
};