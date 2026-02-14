<?php
session_start();

$pseudo = isset($_SESSION['username']) ? $_SESSION['username'] : '';
$continue = isset($_GET['provent']) ? $_GET['provent'] : '/';

session_unset();
session_destroy();
session_write_close();
session_start();

session_regenerate_id(true);

$_SESSION['pseudo'] = $pseudo;

header('location: /');
die();
