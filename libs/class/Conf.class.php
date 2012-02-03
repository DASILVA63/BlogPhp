<?php
if(!defined('IN') OR IN !== True)
    die('fatal error :: you can\'t access this page directly !');

final class Conf {
    
    public static $DB = array(
                            'HOST'      => 'localhost',
                            'NAME'      => 'BlogPHP',
                            'LOGIN'     => 'root',
                            'PASS'      => '',
                            'PREFIX'    => 'lr_',
                        );
    
    public static $SITE = array(
                            'TITRE'        => 'BlogPHP',
                            'CONTACT_MAIL' => 'BlogPHP@sfr.fr',
                            //'MAINTENANCE'  => true,
                            'URL'          => 'http://localhost/BlogPHP/'
                        );
    
    public static $DIRS = array(
                            'ROOT'         => './',
                            'CACHE'        => './cache/',
                            'LIBS'         => './libs/',
                            'PAGES'        => './libs/pages/',
                            'TEMPLATES'    => './templates/',
                            'TEMPLATE'     => './templates/Indigo/',
                        );
    
    public static $MISC = array(
                            'COOKIE_NAME' => 'BlogPHP_data',
                        );
    
    public static $PAGES = array(
                            'home', 'connexion', 'admin', 
                        );
    
    public static $PAGES_ADMIN = array(
                            'home', 'articles', 'coms', 'users'
                        );
}
