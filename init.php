<?php
if(!defined('IN') OR IN !== True)
    die('fatal error :: you can\'t access this page directly !');

//compression de la page
ob_start('ob_gzhandler');
//démarrage de la session
session_start();

/**
*
*   Import des fonctions et class utiles
*
**/
require_once './libs/class/Conf.class.php';
require_once Conf::$DIRS['LIBS'].'functions.php';

/**
*
*   Annulation des magic-quotes
*
**/
if(get_magic_quotes_gpc())
{
    foreach(array('_GET', '_POST', '_COOKIE') as $var)
        ${$var} = (empty(${$var})) ? NULL : array_map('stripslashes', ${$var});
}


/**
*
*   Création des règles de réécriture d'URL
* 
**/
$urls = array(
			array('^(/)?$', Conf::$DIRS['PAGES'].'home.php'),
			array('^index/(?:page/(?P<p>[0-9]+)/)?$', Conf::$DIRS['PAGES'].'home.php'),
			array('^article/(?P<id>[0-9]+)/$', Conf::$DIRS['PAGES'].'home.php'),
			
            array('^contact/$', Conf::$DIRS['PAGES'].'contact.php'),
            
            array('^connexion/$', Conf::$DIRS['PAGES'].'connexion.php'),
            array('^deconnexion/$', Conf::$DIRS['PAGES'].'connexion.php',
                  'extra-params' => array('deconnexion' => True)),
            
            array('^admin/$', Conf::$DIRS['PAGES'].'admin/home.php'),
            
            array('^admin/articles/$', Conf::$DIRS['PAGES'].'admin/articles/list.php'),
            array('^admin/articles/(?:new|edit/(?P<id>[0-9]+))/$', Conf::$DIRS['PAGES'].'admin/articles/write.php'),
            
            array('^admin/commentaires/$', Conf::$DIRS['PAGES'].'admin/coms/list.php'),
            array('^admin/commentaires/edit/(?P<id>[0-9]+)/$', Conf::$DIRS['PAGES'].'admin/coms/write.php'),

            array('^admin/users/$', Conf::$DIRS['PAGES'].'admin/users/list.php'),
            array('^admin/users/(?:new|edit/(?P<id>[0-9]+))/$', Conf::$DIRS['PAGES'].'admin/users/edit.php'),
            
            array('^photos/(?:page/(?P<p>[0-9]+)/)?$', Conf::$DIRS['PAGES'].'photos.php'),
            array('^album/(?P<album>[0-9]+)/$', Conf::$DIRS['PAGES'].'photos.php'),
            
            array('^(?P<slug>\w+)/$', Conf::$DIRS['PAGES'].'home.php'),
        );

$dispatcher = new UrlDispatcher($urls, '/BlogPHP/');

