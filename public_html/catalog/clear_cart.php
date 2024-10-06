<?php
session_start();
unset($_SESSION['CART']);
echo json_encode([]);
?>