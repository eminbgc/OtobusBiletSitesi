<?php

if (session_status() === PHP_SESSION_NONE) {           
    session_start();
}

try {
    $host = "localhost";       
    $dbname = "otobus_db";    
    $username = "root";         
    $password = "";           
    
    
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);     
    
} catch (PDOException $e) {
    echo "Bağlantı hatası: " . $e->getMessage();
    die();
}
?>