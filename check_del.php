<?php
require 'db_config.php';
$cols = $pdo->query('SHOW COLUMNS FROM deliveries')->fetchAll(PDO::FETCH_COLUMN);
echo implode(',', $cols);
