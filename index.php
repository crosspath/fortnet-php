<?php
require 'lib/App.php';
$app = App :: get_app();

$app -> get('/', 'Enters#index');
$app -> get('/api/v1/visits.json', 'Api#visits');
$app -> get('/api/v1/search_people.json', 'Api#search_people');
$app -> get('/:filename.xlsx', 'Enters#export');

$app -> run();
