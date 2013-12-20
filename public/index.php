<?php

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
