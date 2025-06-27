<?php
session_start();
if (!isset($_SESSION['tenant_id'])) {
    header("Location: ../login.php");
    exit();
}
?>