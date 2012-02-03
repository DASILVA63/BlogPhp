<?php
if(!defined('IN') OR IN !== True)
    die('fatal error :: you can\'t access this page directly !');


final class UserManager extends ManagerBase
{
    
    protected static $valid_limiters = array(
                                    'ID'             => 'mID = ?',
                                    'GID'            => 'g_ID = ?',
                                    'LIMIT'          => 'LIMIT %u, %u',
                                    'ACTIVATED'      => 'm.m_activated = ?',
                                    'PSEUDO'         => 'm.m_pseudo = ?',
                                    'PASS'           => 'm.m_pass = ?',
                                    'MAIL'           => 'm.m_mail = ?',
                                );
     
    
    public static function get(array $limiters=array(), $unique=False)
    {
        list($limit, $limiters, $args) = parent::getLimiters($limiters, self::$valid_limiters);
        $limiters = (!empty($limiters)) ? 'WHERE '.$limiters : '';
        
        $query = Site::$DB->query('SELECT m.mID, m.g_ID, m.m_pseudo, m.m_pass, '
        .'m.m_mail, m.m_activated, m.m_inscription, m.m_prenom, m.m_nom, '
        .'DATE_FORMAT(m.m_inscription, \'%e/%c/%Y à %k:%i\') as date_fmt, '
        .'g.gID, g.g_level, g.g_title '
        .'FROM '.Conf::$DB['PREFIX'].'membres m '
        .'INNER JOIN '.Conf::$DB['PREFIX'].'groupes g ON g.gID = m.g_ID '
        .$limiters.' '.$limit, $args) OR Site::$DB->trigger(__LINE__, __FILE__);
        
        $data = array();
        $count = 0;
        $results = $query->fetchAll();
        foreach($results as $result)
        {
            $data[] = new User($result);
            ++$count;
        }
        
        $return = array($count, ($count == 1 AND (strstr($limiters, 'mID') OR $unique)) ? $data[0] : $data);
        
        $query->closeCursor();

        return $return;
    }
    
    public static function count(array $limiters=array())
    {
        list($limit, $limiters, $args) = parent::getLimiters($limiters, self::$valid_limiters);
        $limiters = (!empty($limiters)) ? 'WHERE '.$limiters : '';
        
        $query = Site::$DB->query('SELECT 1 '
        .'FROM '.Conf::$DB['PREFIX'].'membres m '
        .'INNER JOIN '.Conf::$DB['PREFIX'].'groupes g ON g.gID = m.g_ID '
        .$limiters.' '.$limit, $args) OR Site::$DB->trigger(__LINE__, __FILE__);
        
        $count = count($query->fetchAll());
        
        $query->closeCursor();
        
        return $count;
    }
    
    public static function isPseudoAvailable($pseudo)
    {
        return self::count(array('PSEUDO' => $pseudo)) == 0;
    }
    
    public static function isMailAvailable($mail)
    {
        return self::count(array('MAIL' => $mail)) == 0 OR Site::$User->m_mail == $mail;
    }
    
    public static function form_isPseudoAvailable($pseudo)
    {
        return (self::isPseudoAvailable($pseudo)) ? '' : 'Le pseudo « '.$pseudo.' » est déjà utilisé.' ;
    }
    
    public static function form_isMailAvailable($mail)
    {
        return (self::isMailAvailable($mail)) ? '' : 'Le mail « '.$mail.' » est déjà utilisé.' ;
    }
    
    public static function connect(User &$dn)
    {
        $_SESSION['mID'] = (int) $dn->mID;
        Site::$User = $dn;
        
        cookie(Conf::$MISC['COOKIE_NAME'], 
                base64_encode(serialize(array
                (
                 'mID'       => $dn->mID,
                 'pseudo'    => $dn->m_pseudo,
                 'pass'      => $dn->m_pass
                )))
            );
    }
    
    public static function deconnect()
    {
        unset($_SESSION['mID']);
        session_destroy();
        session_unset();
        del_cookie(Conf::$MISC['COOKIE_NAME']);
        
        $anon_user_data = array('mID' => 0);
        Site::$User = new User($anon_user_data);
        
        return True;
    }
    
    public static function tryConnect()
    {
        self::checkSession();
        
        if(!Site::$User->isLoggedIn())
            self::checkCookie();
    }
    
    public static function checkSession()
    {
        if(!empty($_SESSION['mID']) AND is_numeric($_SESSION['mID']))
        {
            list($nb_results, $dn_user) = self::get(array('ID' => $_SESSION['mID']));
            
            if($nb_results == 1 AND $dn_user->m_activated == 1)
                self::connect($dn_user);
        }
    }
    
    public static function checkCookie()
    {
        if(!empty($_COOKIE[Conf::$MISC['COOKIE_NAME']]))
        {
            $cookie = unserialize(base64_decode($_COOKIE[Conf::$MISC['COOKIE_NAME']]));
            
            if(!empty($cookie['pseudo']) AND !empty($cookie['pass']) AND strlen($cookie['pass']) == 40)
            {
                list($nb_results, $dn_user) = self::get(array(
                                                    'PSEUDO'    => $cookie['pseudo'],
                                                    'PASS'      => $cookie['pass'],
                                                ), True);
                
                if($nb_results == 1 AND $dn_user->m_activated == 1)
                    self::connect($dn_user);
            }
        }
    }
    
    public static function save(User &$item)
    {
        return (is_null($item->mID)) ? self::newUser($item) : self::update($item);
    }
    
    private static function newUser(User &$item)
    {
        $args = array($item->g_ID, $item->m_pseudo, $item->m_mail, $item->m_pass);
        
        $query = Site::$DB->query('INSERT INTO '.Conf::$DB['PREFIX'].'membres '
        .'(g_ID, m_pseudo, m_mail, m_pass, m_inscription) VALUES '
        .'(?, ?, ?, ?, NOW())', $args) OR Site::$DB->trigger(__LINE__, __FILE__);
        
        return ($query->rowCount() == 1);
    }
    
    private static function update(User &$item)
    {
        $keys = $item->getKeys();
        
        
        $s_update = $args = array();
        foreach($keys as $key)
        {
            if(!in_array($key, array('pass_confirm', 'mID', 'gID', 'g_level', 'g_title', 'date_fmt')))
            {
                $s_update[] = $key.' = ?';
                $args[] = $item->{$key};
            }
        }
        $args[] = $item->mID;
        
        $query = Site::$DB->query('UPDATE '.Conf::$DB['PREFIX'].'membres SET '.implode(', ', $s_update).' WHERE mID = ?', $args) OR Site::$DB->trigger(__LINE__, __FILE__);
        
        return ($query->rowCount() == 1);
    }
}
?>
