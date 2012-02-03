<?php
if(!defined('IN') OR IN !== True) {
    die( 'fatal error :: you can\'t access this page directly !' );
}

/**
*
*   Fonction d'autoload des class
*
**/

function __autoload($class_name)
{	
    require_once Conf::$DIRS['LIBS'].'class/'.$class_name.'.class.php';	
}

/**
 * Formate une date venant de MySQL
 * 
 * @param string $date :: date à formater
 * @param bool $format :: format de date à utiliser (voir http://www.php.net/manual/fr/function.strftime.php)
 * @return str
 */
function display_db_date($date, $format='%A %d %B à %Hh%M')
{
    return strftime($format, $date);
}

function resize_pic($pic, $dest, $size=200)
{
    list($width, $height, $type, $attr) = getimagesize($pic);
    $ratio = (float) $width/$height;
    
    //moche, mais volontaire
    $thumb_height = $size;
    $thumb_width = ceil($size * $ratio);
    
    $pic_org = imagecreatefromjpeg($pic); // source
    $pic_thumb = imagecreatetruecolor($thumb_width, $thumb_height); //new img
    
    // on redimensionne l'image
    imagecopyresized($pic_thumb, $pic_org, 0, 0, 0, 0, $thumb_width, $thumb_height, $width, $height);
    
    return imagejpeg($pic_thumb, $dest);
}

function printr($array)
{
    echo '<pre style="font-size:150%;">'.print_r($array, true).'</pre>';
}


/**
 * Récupère l'ip du visiteur
 * @return string
 */
function get_ip()
{
    if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    elseif(!empty($_SERVER['HTTP_CLIENT_IP']))
        return $_SERVER['HTTP_CLIENT_IP'];
    else
        return $_SERVER['REMOTE_ADDR'];
}

/**
 * a-t-on une adresse mail valide ?
 * @param string $email :: mail à vérifier
 * @param bool $checkdns :: utilisation des dns
 * @return bool
 */
function is_valide_mail($email, $checkdns = False)
{
    if (preg_match('#^[^@]*?@(.*?\.[a-zA-Z]{2,4})$#i', $email, $match))
    {
        // Les fonctions de vérification de DNS ne tournent pas sous Windows
        if (!$checkdns || preg_match('/^WIN/', PHP_OS))
            return True;

        $check = (function_exists('checkdnsrr')) ? checkdnsrr($match[1]): FALSE;

        // En cas d'échec de la fonction checkdnsrr() (ou tout simplement si elle n'existe pas), on vérifie
        // l'existance du serveur avec fsockopen()
        if (!$check)
        {
            $errno = 0;
            $errstr = '';
            $check = @fsockopen($match[1], 25, $errno, $errstr, 5);
        }
        
        return $check;
    }
    
    return False;
}

/**
 * Envoi d'un mail au format HTML
 * @param string $dest :: adresse mail du destinataire
 * @param string $titre :: sujet du mail
 * @param string $cont :: contenu du mail
 * @return bool
 */
function envoi_mail($dest, $titre, $cont) {
     //----------------------------------------------- 
     //DECLARE LES VARIABLES 
     //----------------------------------------------- 
    $email_reply = 'robot@sfr.fr';

    $message_html = '<html> 
    <head> 
        <title>'.$titre.'</title> 
    </head> 
    <body>
        <div style="padding: 7px; font-size: 1.1em">
            '.$cont.'
            <br />
            <p>
                Passez une bonne journée sur <a href="http://BlogPHP.fr/">'.Conf::$SITE['TITRE'].'</a>,
                <br />
                <em>L\'équipe de développement.</em>
            </p>
        </div>
    </body> 
    </html>'; 

    //----------------------------------------------- 
    //HEADERS DU MAIL 
    //----------------------------------------------- 
	ini_set('SMTP','smtp.sfr.fr');

    $entetedate = date("D, j M Y H:i:s"); // avec offset horaire
    $headers  = 'From: "'.Conf::$SITE['TITRE'].'" <'.$email_reply.'>'."\n";
    $headers .= 'Return-Path: <'.$email_reply.'>'."\n"; 
    $headers .= 'MIME-Version: 1.0'."\n"; 
    $headers .= 'Content-Type: text/html; charset="utf-8"'."\n"; 
    $headers .= 'Content-Transfer-Encoding: 8bit'."\n"; 
    $headers .= "X-Mailer: PHP/" . phpversion() . "\n\n" ;

    return mail($dest, $titre, $message_html, $headers);
}

// -----------------------------------------------
// Récupération du domaine
// -----------------------------------------------
function getDomain($url='') 
{
    if(empty($_SERVER['HTTP_HOST']) AND empty($url))
        return '';
    else 
    {
        $url = (empty($url)) ? $_SERVER['HTTP_HOST'] : $url;
        
        if(strtolower(substr($url, 0, 7)) != 'http://') 
            $url = 'http://'.$url;
        
        return '.'.parse_url($url, PHP_URL_HOST);
    }
}

/*
*setcookie plus rapide
*@$nom :: nom du cookie (string)
*@$valeur :: valeur du cookie (string)
*return bool
*/
function cookie($nom, $valeur) 
{
    return setcookie($nom, $valeur, time() + (86400*30), '/', getDomain());
}

function del_cookie($nom)
{
    return setcookie($nom, '', time() - (86400*30), '/', getDomain());
}

function get_list_page($page, $nb_page, $nb = 3)
{
    $list_page = array();
    for ($i=1; $i <= $nb_page; $i++)
    {
        if (($i < $nb) OR ($i > $nb_page - $nb) OR (($i < $page + $nb) AND ($i > $page - $nb)))
            $list_page[] = $i;
        else
        {
            if ($i >= $nb AND $i <= $page - $nb)
                $i = $page - $nb;
            elseif ($i >= $page + $nb AND $i <= $nb_page - $nb)
                $i = $nb_page - $nb;
                $list_page[] = '...';
        }
    }
    return $list_page;
}
