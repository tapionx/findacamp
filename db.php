<?php

try {
    $db = new PDO('sqlite:db/db.sqlite3');
    $db->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
} catch(PDOException $error) {
    echo $error->getMessage();
}

function query($sql, $data=NULL) {
    try {
        global $db;
        $query = $db->prepare($sql);
        $query->setFetchMode(PDO::FETCH_ASSOC);
        $query->execute($data);
        $res = $query->fetchAll();
        return $res;
    } catch(PDOException $error) {
        echo $error->getMessage();
    }
}

function debug($var){
    header("Content-type: text/plain; charset=utf-8;");
    echo "DEBUG \n";
    print_r($var);
    die('DEBUG');
}

?>

