<?php
 

function isLoggedIn() {
    return isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
}
require_once 'footer_code.php';
?>