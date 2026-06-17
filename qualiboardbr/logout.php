<?php
session_start();
session_destroy(); // Destrói todas as sessões ativas
header("Location: login.php");
exit;
?>