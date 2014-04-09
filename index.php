<!--
/*
 * html5sync Plugin v.0.0.3 (https://github.com/maparrar/html5sync)
 * Feb 2014
 * - maparrar: http://maparrar.github.io
 * - jomejia: https://github.com/jomejia
 */
 -->
 
<?php
session_start();
$_SESSION['html5sync_userId']=15;
$_SESSION['html5sync_role']="role1";
?>
 
<!doctype html>
<html manifest="cache.manifest" type="text/cache-manifest">
    <head>
        <title>html5sync</title>
        <meta charset="utf-8"/>
        <link rel="stylesheet" type="text/css" href="html5sync/client/css/base.css">
        <script src="html5sync/client/jquery/jquery-2.1.0.min.js"></script>
        <script src="html5sync/client/core/Html5Sync.js"></script>
        <script src="html5sync/client/core/Database.js"></script>
        <script type="text/javascript">
            $( document ).ready(function(){
                var html5Sync=new Html5Sync({
                    debugging:true,
                    showState:true
                },function(err){
                    console.debug(err);
                });
                
               
                $("#reloadData").click(function(){
                    html5Sync.forceReload(function(err){
                        if(err){
                            console.debug(err);
                        }
                    });
                });
            });
        </script>
    </head>
    <body>
        <input type="button" id="reloadData" value="Recargar datos"/>
    </body>
</html>
