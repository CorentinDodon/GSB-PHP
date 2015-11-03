<?php

$app->get('/', function () use ($app) {
	$url=$app["url_generator"]->generate("index");
	$test=substr($url,0,9)."index.php/".substr($url,9);
    return $app->redirect($test);
});

$app->get('/index.php/admin/accueil', function () use ($app) {
	$url=$app["url_generator"]->generate("index");
    return $app->redirect($url);
});

$app->get('/login', "GSB\Controller\LoginController::loginAction")
	->bind('login');
$app->get('/admin/accueil', "GSB\Controller\LoginController::indexAction")
	->bind('index');
$app->get('/admin/praticien', "GSB\Controller\PraticienController::indexAction")
	->bind('listPraticien');
$app->get('/admin/medicament', "GSB\Controller\MedicamentController::indexAction")
	->bind('listMedicament');
$app->get('/admin/visiteur', "GSB\Controller\VisiteurController::indexAction")
	->bind('listVisiteur');
$app->get('/admin/rapport', "GSB\Controller\RapportController::indexAction")
	->bind('listRapport');
$app->match('/admin/rapport/add', "GSB\Controller\RapportController::addRapportAction")
	->bind('addRapport');