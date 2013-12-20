<<<<<<< HEAD
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


<VirtualHost *:80>
    ServerName friday-sprint-20131220.local
    ServerAdmin ivan@softwaremarbles.com
    DocumentRoot /var/www/friday-sprint20131220/public
    <Directory /var/www/friday-sprint20131220/public>
        AllowOverride All
        Order deny,allow
        Allow from All
    </Directory>
</VirtualHost>
=======
#!/usr/bin/php
<?php
$data = array(
    'format' => 'json',
    'status' => 'live'
    );
echo json_encode($data);
?>

>>>>>>> d69b9e5129f440ed7c33abdba0f317ec91488452
