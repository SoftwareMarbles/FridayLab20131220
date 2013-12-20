<?php

ini_set("include_path", ".:../:../epiphany/src/");

echo 'Hellllooooaaaa\n';

include 'Epi.php';

Epi::setPath('base', realpath(dirname(__FILE__)) . "/..");

Epi::init('route');
getRoute()->get('/', 'home');
getRoute()->get('/contact', 'contactUs');
getRoute()->run();

function home() {
    echo 'You are at the home page';
}

function contactUs() {
    echo 'Send us an email at <a href="mailto:ivan@softwaremarbles.com">ivan@softwaremarbles.com</a>';
}

?>
