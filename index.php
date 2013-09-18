<?php
require 'lib/app.php';
$app = App :: get_app();

$app -> get('/', Route :: act('Enters#index'));

$app -> run();
