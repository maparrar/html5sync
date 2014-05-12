<?php
session_start();
$_SESSION['html5sync_userId']=6;
$_SESSION['html5sync_role']="role1";
?>
<!--
/*
 * html5sync Plugin v.0.0.3 (https://github.com/maparrar/html5sync)
 * Feb 2014
 * - maparrar: http://maparrar.github.io
 * - jomejia: https://github.com/jomejia
 */
 -->
 
<!doctype html>
<html manifest="cache.manifest" type="text/cache-manifest">
    <head>
        <title>html5sync</title>
        <meta charset="utf-8"/>
        <link rel="stylesheet" type="text/css" href="html5sync/client/css/base.css">
        <script src="html5sync/client/jquery/jquery-2.1.0.min.js"></script>
        <script src="html5sync/client/core/Configurator.js"></script>
        <script src="html5sync/client/core/Connector.js"></script>
        <script src="html5sync/client/core/Html5Sync.js"></script>
        <script src="html5sync/client/core/Database.js"></script>
        <script type="text/javascript">
            $( document ).ready(function(){
                var html5Sync=new Html5Sync({
                    debugging:true,
                    showState:true,
                    syncCallback:functionToExecEachSync
                },function(err){
                    if(err){
                        console.debug(err);
                    }else{
                        debug("----------- HTML5SYNC - NO ERRORS -----------","good");
                        //safe use of database... put your code here
                        
                        $("#search").click(function(){
                            functionToExecEachSync();
                        });
                    }
                });
                $("#reloadData").click(function(){
                    html5Sync.forceReload(function(err){
                        if(err){
                            console.debug(err);
                        }
                    });
                });
                
                
                
                
                /**
                 * Function to execute each sync
                 */
                function functionToExecEachSync(){
                    var store=$.trim($("#store").val());
                    var key=$.trim($("#key").val());
                    if(store!==""&&key!==""){
                        html5Sync.database.get(store,key,function(err,objects){
                            if(!err&&objects.length>0){
                                for(var i in objects){
                                    $("#objects").text(objToString(objects[i]));
                                }
                            }else{
                                $("#objects").text("");
                            }
                        });
                    }
                }
                function objToString (obj) {
                    var str = '';
                    for (var p in obj) {
                        if (obj.hasOwnProperty(p)) {
                            str += p + '::' + obj[p] + '\n';
                        }
                    }
                    return str;
                };

            });
        </script>
    </head>
    <body>
        <input type="button" id="reloadData" value="Recargar datos"/>
        <div id="example">
            <br><br>
            <input id="store" type="text" placeholder="store name"/>
            <input id="key" type="text" placeholder="key"/>
            <input id="search" type="button" value="search"><br>
            <textarea id="objects" placeholder="objects" style="height: 200px;width: 500px;"></textarea>
        </div>
    </body>
</html>
