<?php

    try{
        //Host
        define("HOST", "localhost");

        //dbName
        define("DBNAME", "coffee-shop");

        //user
        define("USER", "root");

        //password
        define("PASS", "");
        $conn = new PDO("mysql:host = ".HOST.";dbname = ".DBNAME."",USER,PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    }catch(PDOException $Exception){
        echo $Exception->getMessage() ;

    }

?>
