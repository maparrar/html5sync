<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


echo("Pruebas");

?>
<html>
    <head>
        <title>html5sync</title>
        
        <script src="scripts/jquery-2.1.0.min.js"></script>
        
        <link rel="stylesheet" type="text/css" href="scripts/base.css">
        
        <script type="text/javascript">
            $( document ).ready(function(){
                if(window["indexedDB"] === undefined) {
                    console.log("Este navegador no soporta indexDB");
                }else{
                    debug("indexDB soportado...");
                    
                    debug("indexDB soportado...ssssssssssssss");
                    
                    console.debug(Storage);
                }










                
                
                
                
                
                
                
                var db;
                var request = indexedDB.open("html5sync");

                request.onupgradeneeded = function() {
                    // The database did not previously exist, so create object stores and indexes.
                    db = request.result;
                    var store = db.createObjectStore("music", {keyPath: "id"});
                    var songIndex = store.createIndex("by_song", "song", {unique: true});
                    var interpreterIndex = store.createIndex("by_interpreter", "interpreter");
                    var albumIndex = store.createIndex("by_album", "album");

                    // Populate with initial data.
                    store.put({id: 1, interpreter: "Tom Yorke", song: "Analyse", album: "The eraser"});
                    store.put({id: 2, interpreter: "Bob Marly", song: "One love", album: "Legend"});
                    store.put({id: 3, interpreter: "Alice in Chains", song: "Angry Chair", album: "Unplugged"});
                    
                    
                    
                    var tx = db.transaction("music", "readwrite");
                    var store = tx.objectStore("music");

                    store.put({id: 4, interpreter: "Fito Paez", song: "Circo Beat", album: "Circo Beat"});
                    store.put({id: 5, interpreter: "Urge Overkill", song: "Girl you'll be a woman soon", album: "Stull"});

                    tx.oncomplete = function() {
                      // All requests have succeeded and the transaction has committed.
                    };
                };

                request.onsuccess = function() {
                    db = request.result;
                };
                
                db.onerror = function(event) {
                    // Generic error handler for all errors targeted at this database's
                    // requests!
                    alert("Database error: " + event.target.errorCode);
                };




                

















                function debug(message){
                    $("#debug").append(message+"<br>");
                }
                
            });
        </script>
    </head>
    <body>
        <section id="debug"></section>
    </body>
</html>
