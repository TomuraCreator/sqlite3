<?php
declare(strict_types = 1);
include 'autoload.php';
include 'config.php';

try {
    $client = new classes\Client();
    $shop = new classes\Shop();
//    var_dump($client->insert(["phone", "name"], [9811644988, "Фёдор Тютчев"]));
//    var_dump($client->update(3, [3, 9843211245, 'Роберт Семёныч']));
//    var_dump($client->find(4));
    var_dump($client->find(19));
    var_dump($shop->find(1));
} catch(Exception $e) {
    echo $e->getMessage();
}


