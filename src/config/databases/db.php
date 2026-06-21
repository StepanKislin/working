<?php
// config data
$host = 'localhost';
$db = 'Working';
$user = 'root';
$pass = 'root';

// mysqli connect function
$conn = new mysqli($host, $user, $pass, $db);

// try connect
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}




    
?>
