<?php
error_reporting(E_ALL);//dev only
$time = microtime(true);

//servira pour empecher que l'on accède directement à n'importe quel fichier
define('IN', true);

/**
*
*   Initialisation du site
*
**/
require_once './init.php';
Site::init();


/**
*
*   Traitement de l'url selon les règles de réécriture
*
**/

try {
    $dispatcher->handle();
} catch (Error404 $e) {
    Site::redirect(Conf::$SITE['URL'], 'La page que vous demandez n\'existe pas ou plus.');
}


/**
*
*   Chargement de la page demandée
*
**/

$page = new Page;


/**
*
*   Affichage de la page
*
**/

Site::display($page);


$time = round(microtime(true) - $time, 4);
//echo 'Page générée en '.$time.' secondes';
ob_end_flush();

