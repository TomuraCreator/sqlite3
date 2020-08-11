<?php
spl_autoload_register( function(string $name) {
    $replace_string = str_replace('\\', "/", $name);
    include "./$replace_string.php";
});