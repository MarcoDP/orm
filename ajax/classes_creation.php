<?php

    session_start();

    $dir = $_SERVER['DOCUMENT_ROOT'] . '/classes/';
    
    // CREO LA CARTELLA CONTENITORE
    if(file_exists($dir)){
        $files = scandir($dir);
        foreach ($files as $file) {
            if($file !== '.' && $file !== '..'){
                unlink($dir.$file);
            }
        } 
    } else {
        mkdir($dir, 0775);
    }
    
    $host = filter_input(INPUT_POST, 'host', FILTER_SANITIZE_STRING);
    $db = filter_input(INPUT_POST, 'db', FILTER_SANITIZE_STRING);
    $port = filter_input(INPUT_POST, 'port', FILTER_SANITIZE_STRING);
    $user = filter_input(INPUT_POST, 'user', FILTER_SANITIZE_STRING);
    $pass = filter_input(INPUT_POST, 'pass', FILTER_SANITIZE_STRING);

    // CONTROLLO VARIABILI
    if( empty($host) || empty($db) || empty($port) || empty($user) || empty($pass) ) {
        $json['status'] = FALSE;
        $json['message'] = "I dati per la connessione al DataBase sono necessari per la crezione del file: MySQL_Connect.php";
        echo json_encode($json);
        exit();
    }
    
    // CREO LA CLASSE PER LA CONNESSIONE
    $fileClass = fopen($dir . 'MySQL_Connect.php', "w") or die("Impossibile creare il file!");
    $txt = "<?php\n";
    $txt .= "\n";
    $txt .= "class MySQL {\n";
        $txt .= "\n";
        $txt .= "\tfunction Connection() {\n";
            $txt .= "\n";
            $txt .= "\t\t\$connString = 'mysql:host=$host;port=$port;dbname=$db';\n";
            $txt .= "\n";
            $txt .= "\t\t\$options = array(PDO::MYSQL_ATTR_INIT_COMMAND  => \"SET NAMES 'UTF8'\", PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC);\n";
            $txt .= "\n";
            $txt .= "\t\ttry {\n";
                $txt .= "\t\t\t\$conn = new PDO(\$connString, '$user', '$pass', \$options);\n";
            $txt .= "\t\t}\n";
            $txt .= "\t\tcatch(PDOException \$e) {\n";
                $txt .= "\t\t\tthrow new PDOException(\$e);\n";
            $txt .= "\t\t}\n";
            $txt .= "\n";
            $txt .= "\t\t\$conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);\n";
            $txt .= "\n";
            $txt .= "\t\treturn \$conn;\n";
            $txt .= "\n";
        $txt .= "\t}\n";
        $txt .= "\n";
    $txt .= "}";
    
    fwrite($fileClass, $txt) or die("Impossibile scrivere il file!");
    fclose($fileClass);
    
    if(is_array($_POST['tables']) && count($_POST['tables']) > 0){
        
        require_once $_SERVER['DOCUMENT_ROOT'] . '/Classes/MySQL_Connect.php';
        
        $mysql = new MySQL();
        $conn = $mysql->Connection();
        
        // CREO LA CLASSE PER OGNI TABELLA SELEZIONATA
        foreach ($_POST['tables'] as $tabella) {

            $tablename = $tabella['tablename'];
            $classname = $tabella['classname'];

            $classname = ($classname ?: $tablename);
            
            $sql = $conn->prepare('SHOW FIELDS IN ' . $tablename);
            $sql->execute();
            $campi = $sql->fetchAll();
            
            $filename = 'Class_'.ucfirst($classname).'.php';
            $fileClass = fopen($dir . $filename, "w") or die("Impossibile creare il file!");
            
            $txt = "<?php\n";
            $txt .= "\n";
            
            // REQUIRE CLASSE CONNESSIONE
            $txt .= "class ".ucfirst($classname)." {\n";
                $txt .= "\n";
                
                // STRUTTURA LE PROPRIETA' DI TABELLA
                foreach ($campi as $campo) {
                    
                    $field = ucfirst($campo['Field']);
                    $txt .= "\tprotected $$field;\n";
                    
                    // SET DELLA VARIABILE '$PK' PER LE OPERAZIONI: 'READ, UPDATE, DELETE'
                    if($campo['Key'] === 'PRI') $PK = $campo['Field'];
                    
                }
                $txt .= "\n";
                
                // CONNECTION
                $txt .= "\tprivate \$conn;\n";
                $txt .= "\n";
                $txt .= "\tfunction __construct() {";
                    $txt .= "\n";
                    $txt .= "\t\t\$conn = new MySQL();\n";
                    $txt .= "\t\t\$this->conn = \$conn->Connection();\n";
                $txt .= "\t}\n";
                $txt .= "\n";
                
                foreach ($campi as $campo) {
                    
                    $field = ucfirst($campo['Field']);
                    
                    $txt .= "\tpublic function get$field(){\n";
                        $txt .= "\t\treturn \$this->$field;\n";
                    $txt .= "\t}\n";
                    $txt .= "\n";
                    
                    // LA PRIMARY KEY NON DEVE AVERE IL METODO 'SET'
                    if($campo['Key'] === 'PRI') continue;
                        
                    $txt .= "\tpublic function set$field(\$value){\n";
                        $txt .= "\t\t\$this->$field = \$value;\n";
                    $txt .= "\t}\n";
                    $txt .= "\n";
                    
                }
                
                // INSERT
                $txt .= "\tprivate function insert(){\n";
                    $txt .= "\t\ttry{\n";
                        $txt .= "\t\t\t\$sql = \$this->conn->prepare('INSERT INTO $tablename SET";

                            foreach ($campi as $campo) {

                                if($campo['Key'] === 'PRI') continue;
                                $field = $campo['Field'];
                                $txt .= " $field = :$field,";

                            }

                            $txt = substr($txt, 0, -1);

                        $txt .= "');\n";

                        foreach ($campi as $campo) {

                            if($campo['Key'] === 'PRI') continue;
                            $field = $campo['Field'];
                            $proprieta = ucfirst($field);
                            $txt .= "\t\t\t\$sql->bindParam(':$field', \$this->$proprieta);\n";

                        }

                        $txt .= "\t\t\t\$sql->execute();\n";
                        $txt .= "\t\t\t\$this->".ucfirst($PK)." = \$this->conn->lastInsertId();\n";
                    $txt .= "\t\t}\n";
                    $txt .= "\t\tcatch(PDOException \$e) {\n";
                        $txt .= "\t\t\tthrow new PDOException(\$e);\n";
                    $txt .= "\t\t}\n";
                $txt .= "\t}\n";
                $txt .= "\n";
                
                // UPDATE
                $txt .= "\tprivate function update(){\n";
                    $txt .= "\t\ttry{\n";
                        $txt .= "\t\t\t\$sql = \$this->conn->prepare('UPDATE $tablename SET";

                        foreach ($campi as $campo) {

                            if($campo['Key'] === 'PRI') continue;
                            $field = $campo['Field'];
                            $txt .= " $field = :$field,";

                        }

                        $txt = substr($txt, 0, -1);

                        $txt .= " WHERE $PK = :$PK');\n";

                        foreach ($campi as $campo) {

                            $field = $campo['Field'];
                            $proprieta = ucfirst($field);
                            $txt .= "\t\t\t\$sql->bindParam(':$field', \$this->$proprieta);\n";

                        }

                        $txt .= "\t\t\t\$sql->execute();\n";
                    $txt .= "\t\t}\n";
                    $txt .= "\t\tcatch(PDOException \$e) {\n";
                        $txt .= "\t\t\tthrow new PDOException(\$e);\n";
                    $txt .= "\t\t}\n";
                $txt .= "\t}\n";
                $txt .= "\n";
                
                // METODO SAVE
                $txt .= "\tpublic function save(){\n";
                    $txt .= "\t\t(\$this->".ucfirst($PK).") ? \$this->update() : \$this->insert();\n";
                    $txt .= "\t\treturn true;\n";
                $txt .= "\t}\n";
                $txt .= "\n";
                
                // METODO DELETE
                $txt .= "\tpublic function delete(){\n";
                    $txt .= "\t\ttry{\n";
                        $txt .= "\t\t\t\$sql = \$this->conn->prepare('DELETE FROM $tablename WHERE $PK = :$PK');\n";
                        $txt .= "\t\t\t\$sql->bindParam(':$PK', \$this->".ucfirst($PK).");\n";
                        $txt .= "\t\t\t\$sql->execute();\n";
                        $txt .= "\t\t\treturn true;\n";
                    $txt .= "\t\t}\n";
                    $txt .= "\t\tcatch(PDOException \$e) {\n";
                        $txt .= "\t\t\tthrow new PDOException(\$e);\n";
                    $txt .= "\t\t}\n";
                $txt .= "\t}\n";
                $txt .= "\n";
                
                // METODO FINDPK
                $txt .= "\tpublic function findPK(\$value){\n";
                    $txt .= "\t\ttry{\n";
                        $txt .= "\t\t\t\$sql = \$this->conn->prepare('SELECT * FROM $tablename WHERE $PK = :$PK');\n";
                        $txt .= "\t\t\t\$sql->bindParam(':$PK', \$value);\n";
                        $txt .= "\t\t\t\$sql->execute();\n";
                        $txt .= "\t\t\tif(\$sql->rowCount()===1){\n";
                            $txt .= "\t\t\t\t\$data = \$sql->fetch();";
                            $txt .= "\n";
                            $txt .= "\t\t\t\tforeach(\$data as \$key => \$value){\n";
                                $txt .= "\t\t\t\t\t\$this->{ucfirst(\$key)} = \$value;\n";
                            $txt .= "\t\t\t\t}\n";
                            $txt .= "\t\t\t\treturn true;\n";
                        $txt .= "\t\t\t} else {\n";
                        $txt .= "\t\t\t\treturn false;\n";
                        $txt .= "\t\t\t}\n";
                    $txt .= "\t\t}\n";
                    $txt .= "\t\tcatch(PDOException \$e) {\n";
                        $txt .= "\t\t\tthrow new PDOException(\$e);\n";
                    $txt .= "\t\t}\n";
                $txt .= "\t}\n";
                $txt .= "\n";
                
                // METODO FIND
                $txt .= "\tpublic function find(){\n";
                    $txt .= "\t\ttry{\n";
                        $txt .= "\t\t\t\$sql = \$this->conn->prepare('SELECT * FROM $tablename');\n";
                        $txt .= "\t\t\t\$sql->execute();\n";
                        $txt .= "\t\t\treturn \$sql->fetchAll();\n";
                    $txt .= "\t\t}\n";
                    $txt .= "\t\tcatch(PDOException \$e) {\n";
                        $txt .= "\t\t\tthrow new PDOException(\$e);\n";
                    $txt .= "\t\t}\n";
                $txt .= "\t}\n";
                $txt .= "\n";
                
                // METODO TOJSON
                $txt .= "\tpublic function toJSON(){\n";
                    $txt .= "\t\t\$data = array(";
                        foreach ($campi as $campo) {
                            
                            $proprieta = ucfirst($campo['Field']);
                            
                            $txt .= "'$proprieta'=>\$this->$proprieta,";
                            
                        }
                        $txt = substr($txt, 0, -1);
                    $txt .= ");\n";
                    $txt .= "\t\treturn json_encode(\$data);\n";
                $txt .= "\t}\n";
                $txt .= "\n";

                // METODO TOARRAY
                $txt .= "\tpublic function toArray(){\n";
                    $txt .= "\t\t\$data = array(";
                        foreach ($campi as $campo) {
                            
                            $proprieta = ucfirst($campo['Field']);
                            
                            $txt .= "'$proprieta'=>\$this->$proprieta,";
                            
                        }
                        $txt = substr($txt, 0, -1);
                    $txt .= ");\n";
                    $txt .= "\t\treturn \$data;\n";
                $txt .= "\t}\n";
                $txt .= "\n";
                
                // METODO FINDBY
                foreach ($campi as $campo) {

                    if($campo['Key'] === 'PRI') continue;
                    $field = $campo['Field'];
                    $proprieta = ucfirst($field);
                    
                    $txt .= "\tpublic function findBy$proprieta(\$value){\n";
                        $txt .= "\t\ttry{\n";
                            $txt .= "\t\t\t\$sql = \$this->conn->prepare('SELECT * FROM $tablename WHERE $field = :$field');\n";
                            $txt .= "\t\t\t\$sql->bindParam(':$field', \$value);\n";
                            $txt .= "\t\t\t\$sql->execute();\n";
                            $txt .= "\t\t\treturn \$sql->fetchAll();\n";
                        $txt .= "\t\t}\n";
                        $txt .= "\t\tcatch(PDOException \$e) {\n";
                            $txt .= "\t\t\tthrow new PDOException(\$e);\n";
                        $txt .= "\t\t}\n";
                    $txt .= "\t}\n";
                    $txt .= "\n";

                }
                
            $txt .= "}";
            
            fwrite($fileClass, $txt) or die("Impossibile scrivere il file!");
            fclose($fileClass);
            
            $json['classe'][] = $filename;

        }

        array_unshift($json['classe'], 'MySQL_Connect.php');
        $json['status'] = TRUE;
        echo json_encode($json);
        
    } 