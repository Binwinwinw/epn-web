<?php
declare(strict_types=1);

$query = $_GET ? ('?' . http_build_query($_GET)) : '?page=landingpage';
header('Location: public/index.php' . $query);
exit;
