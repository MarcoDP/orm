<?php

    $host = filter_input(INPUT_POST, 'host', FILTER_SANITIZE_STRING);
    $db = filter_input(INPUT_POST, 'db', FILTER_SANITIZE_STRING);
    $port = filter_input(INPUT_POST, 'port', FILTER_SANITIZE_STRING);
    $user = filter_input(INPUT_POST, 'user', FILTER_SANITIZE_STRING);
    $pass = filter_input(INPUT_POST, 'pass', FILTER_SANITIZE_STRING);
    
    $connString = "mysql:host=$host;port=$port;dbname=$db";
    
    $options = array(PDO::MYSQL_ATTR_INIT_COMMAND  => 'SET NAMES \'UTF8\'');

    try {
        
        $conn = new PDO($connString, $user, $pass, $options);
        $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        
        $sql = $conn->prepare('SHOW TABLES IN ' . $db);
        $sql->execute();
        $tabelle = $sql->fetchAll(PDO::FETCH_ASSOC);
        
        $json['status'] = TRUE;
        foreach($tabelle as $tabella){
            $json['data'][] = $tabella["Tables_in_$db"];
            
        }
    
        echo json_encode($json);
        
    }
    catch(PDOException $e) {
        $json['status'] = FALSE;
        $json['message'] = $e->getMessage();
        echo json_encode($json);
    }