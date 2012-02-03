<?php
if(!defined('IN') OR IN !== True)
    die('fatal error :: you can\'t access this page directly !');

final class Site {
    
    static $TPL              = Null;    // objet de gestion des TPL
    static $DB               = Null;    // objet de gestion de la BDD
    static $User             = Null;    // objet représentant l'utilisateur courant
    
    private static $tpl_to_parse    = '';      // fichier tpl à parser
    private static $javascript      = array(); // array contenant le JS à ajouter dans la page
    
    public static function init()
    {
        $anon_user_data = array('mID' => 0);
        
        self::$TPL  = new Talus_TPL(Conf::$DIRS['TEMPLATE'], Conf::$DIRS['CACHE']);
        self::$DB   = new DB(Conf::$DB['HOST'], Conf::$DB['LOGIN'], Conf::$DB['PASS'], Conf::$DB['NAME']);
        self::$User = new User($anon_user_data);
        
        UserManager::tryConnect();
        
        setlocale(LC_ALL, 'fr_FR.utf8', 'fr_FR');
    }
    
    public static function addJavascript($js, $priorite = 20)
    {
        if(is_array($js))
        {
            foreach($js AS $file)
                self::addJavascript($file[0], ((!empty($file[1])) ? $file[1] : $priorite));
        }
        else
            self::$javascript[$priorite][] = '<script type="text/javascript" src="'.(file_exists($js) ? $js : Conf::$SITE['URL'].'libs/js/'.$js).'"></script>'."\n";
    }
    
    //actuallement, bug si usage != unique <-- To Fix
    public static function getJavascript($jscript = '')
    {
        static $js_final = '';
        
        $todo = (empty($jscript)) ? self::$javascript : $jscript;
        
        ksort($todo, SORT_ASC);
        
        foreach($todo as $js)
        {
            if(is_array($js))
                self::getJavascript($js);
            else
                $js_final .= $js;
        }
        
        return $js_final;
    }
    
    public static function setTPLFile($file)
    {
        self::$tpl_to_parse = (string) $file;
    }
    
    public static function getTPLFile()
    {
        return self::$tpl_to_parse;
    }
    
    public static function setTitle($title)
    {
        self::$TPL->set('PAGE_TITLE', $title);
    }
    
    public static function redirect($page, $message = 'Erreur !', $temps = 4)
    {
        self::setTPLFile('redirection.html');
        self::$TPL->set(array(
                    'URL'          => $page,
                    'ADD_HEADER'   => '<meta http-equiv="refresh" content="'.intval($temps).';url='.$page.'" />',
                    'MESSAGE'      => $message,
                    'PAGE_TITLE'   => 'Redirection ...',
               ));
        
        self::_doDisplay();
        exit(0);// on arrête l'exécution de la page
    }
    
    public static function display(Page $page)
    {
        $page->main();
        
        self::_doDisplay();
    }
    
    private static function _doDisplay()
    {
        self::$TPL->set(array(
                        'TPL_DIR'      => Conf::$DIRS['TEMPLATE'],
                        'SITE_URL'     => Conf::$SITE['URL'],
                        'SITE_TITLE'   => Conf::$SITE['TITRE'],
                        'IS_LOGGED_IN' => self::$User->isLoggedIn(),
                        'IS_ADMIN'     => self::$User->isAdmin(),
                        'JAVASCRIPT'   => self::getJavascript(),
                    ));
        
        
        //parsage et affichage de la page
        self::$TPL->parse(self::$tpl_to_parse);
    }
	
	
}
