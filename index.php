<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ./Login');
    exit();
}
else{
    header('Location: ./Dashboard');
}
?>