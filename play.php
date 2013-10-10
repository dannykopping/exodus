<?php
use Exodus\Exodus;
use Exodus\Server;

require 'vendor/autoload.php';

$e = new Exodus(
    new Server('schema_new', '127.0.0.1', 'root', 'root'),
    new Server('schema_new', '33.33.33.10', 'root', 'root')
);

echo 'Forward'.PHP_EOL;
print_r($e->getSQLDiff(Exodus::FORWARD_DIFF, true));
echo PHP_EOL;
echo 'Backward'.PHP_EOL;
print_r($e->getSQLDiff(Exodus::BACKWARD_DIFF, true));
echo PHP_EOL;
die();