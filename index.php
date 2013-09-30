<?php
require 'lib/app.php';
$app = App :: get_app();

$app -> get('/', Route :: act('Enters#index'));
$app -> get('/api/v1/visits.json', Route :: act('Api#visits'));
$app -> get('/api/v1/search_people.json', Route :: act('Api#search_people'));
$app -> get('/:filename.xlsx', Route :: act('Enters#export'));

$app -> run();
