<?php

if (php_sapi_name() !== "cli")
{
  die();
}

if ($argc != 2) exit();

$dir = $argv[1];


include(realpath(dirname(__FILE__) . "/../functions/functions.process.php"));

$p = is_process_running();

if ($p)
{
        end_process($p);
}

start_process(realpath(dirname(__FILE__) . "/process.php") . " " . $dir);

?>
