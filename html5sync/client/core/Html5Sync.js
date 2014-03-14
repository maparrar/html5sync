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
    self.state;         //{bool} Estado de la conexión con el servidor.
    self.showLoadingCounter=0; //{int} Alamacena la cantidad de llamados a showLoading
    self.userId=false;  //Identificador del usuario      
    /**************************************************************************/
    /********************* CONFIGURATION AND CONSTRUCTOR **********************/
    /**************************************************************************/
    //Se mezclan los parámetros por defecto con los proporcionados por el usuario
    //y se agregan a la variable self (this del objeto)
    var def = {
        debugging:false,
        html5syncFolder:"html5sync/",
        stateTimer: 10000,
        showState:false
    };
    self.params = $.extend(def, params);
    /**
     * Método privado que se ejecuta automáticamente. Hace las veces de constructor
     */
    var Html5Sync = function() {
        //Estructura el código HTML5
        setStructure();
        
        //Inicia el proceso de sincronización
        startSync();
        
        
    }();
    
    /**************************************************************************/
    /****************************** SYNC METHODS ******************************/
    /**************************************************************************/
    /**
     * Inicia el proceso de sincronización.
     */
    function startSync(){
        showLoading(false);
        sync();
        setInterval(function(){
            try{
                sync();
            }catch(e){
                setState(false); 
            }
        },self.params.stateTimer);
    };
    /**
     * Verifica si la conexión con el servidor está activa y actualiza el 
     * indicador de estado. Verifica si hay cambios en las tablas para el usuario,
     * si los hay, retorna los cambios.
     * @param {fucntion} callback Función para retornar los resultados
     */
    function sync(callback){
        showLoading(true);
        $.ajax({
            url: self.params.html5syncFolder+"server/ajax/sync.php"
        }).done(function(response) {
            var data=JSON.parse(response);
            var state=(data.state==="true")?true:false;
            var changesInStructure=(data.changesInStructure==="true")?true:false;
            var changesInData=(data.changesInData==="true")?true:false;
            //Marca como conectado
            setState(state);
            //Si hay cambios en la estructura o en los datos se deben recargar
            if(changesInStructure){
                updateStructure();
            }else{
                if(changesInData){
                    updateData();
                }
            }
            showLoading(false);
            if(callback)callback(false);
        }).fail(function(){
            setState(false);
            showLoading(false);
        });
    };
    
    /**
     * Carga de nuevo la estructura de la base de datos por medio de ajax
     * @param {fucntion} callback Función para retornar los resultados
     */
    function updateStructure(callback){
        debug("..::DB INFO::.. Se detectaron cambios en la estructura de las tablas de la BD. Actualizando...");
        $.ajax({
            url: self.params.html5syncFolder+"server/ajax/updateStructure.php"
        }).done(function(response) {
            var data=JSON.parse(response);
            self.userId=parseInt(data.userId);
            var database=data.database;
            var version=parseInt(data.version);
            var tables=data.tables;
            var parameters=parseDatabaseParameters(database,version,tables);
            var database=new Database(parameters,function(err){
                if(err){
                    if(callback)callback(err);
                }else{
                    if(callback)callback(false);
                    //Actualiza los datos luego de actualizar la estructura
                    updateData(function(err){
                        if(err){
                            if(callback)callback(err);
                        }
                    });
                }
            });
        }).fail(function(){
            callback(new Error("Unable to connect the server to update structure"));
            setState(false);
        });
    };
    /**
     * Carga la información de las tablas cuando se detectan cambios
     * @param {fucntion} callback Función para retornar los resultados
     */
    function updateData(callback){
        debug("..::DB INFO::.. Se detectaron cambios en los datos de la BD. Actualizando...");
        $.ajax({
            url: self.params.html5syncFolder+"server/ajax/updateData.php"
        }).done(function(response) {
            
            console.debug(response);
            
            if(callback)callback(false);
        }).fail(function(){
            if(callback)callback(new Error("Unable to reload data from the server"));
            setState(false);
        });
    };
    
    
    
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
        window.debug=function(message){
            if(self.params.debugging){
                if(!$("#html5sync_debug").exist()){
                    $("body").prepend('<div id="html5sync_debug"></div>');
                }
                $("#html5sync_debug").append(message+"<br>");
                $("#html5sync_debug").scrollTop($('#html5sync_debug').get(0).scrollHeight);
            }
        };
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
     * @param {string} database Nombre de la base de datos
     * @param {int} version Número de versión
     * @param {array} tables Lista de tablas a convertir
     * @returns {array} Lista de parámetros para pasar a la base de datos
     */
    function parseDatabaseParameters(database,version,tables){
        var stores=new Array();
        for(var i in tables){
            stores.push(tableToStore(tables[i]));
        }
        //Lista de parámetros que define la configuración de la base de datos
        return {
            database: "html5sync_"+database+"_"+self.userId,
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
    /**************************************************************************/
    /***************************** PUBLIC METHODS *****************************/
    /**************************************************************************/
    /**
     * Inserta objetos en un almacén. Recibe un objeto o un array de objetos
     * @param {string} storeName Nombre del almacén de datos donde se quiere insertar la información
     * @param {object[]} data Objeto o array de objetos
     * @param {function} callback Función a la que se retornan los resultados
     */
    self.publicFunction=function(){
        
    };
    
    
    /**
     * Carga los datos de las tablas permitidas. Toda la información de carga
     * está especificada en el archivo de configuración:
     * html5sync/server/config.php
     */
    function loadData(){
        try{
            showLoading(true);
            $.ajax({
                url: self.params.html5syncFolder+"server/ajax/loadData.php"
            }).done(function(response) {
//                console.debug(response);
                showLoading(false);
            }).fail(function(){
                showLoading(false);
            });
        }catch(e){
            setState(false); 
        }
    };
};