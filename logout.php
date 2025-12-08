<?php

require_once 'includes/functions.php';

delete_remember_cookie();

$_SESSION = array();
session_destroy();

header('Location: login.php');
exit;