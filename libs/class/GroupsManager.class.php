<?php
if(!defined('IN') OR IN !== True)
    die('fatal error :: you can\'t access this page directly !');

final class GroupsManager extends ManagerBase {
    
    private static $valid_limiters = array(
                                    'ID'     => 'gID = ?',
                                    'LEVEL'  => 'g_level = ?'
                                );
    
    
    public static function get(array $limiters=array(), $force_array=False)
    {
        list($limit, $limiters_text, $args) = parent::getLimiters($limiters, self::$valid_limiters);
        $limiters_text = (!empty($limiters_text)) ? 'WHERE '.$limiters_text : '';
        
        $query = Site::$DB->query('SELECT gID, g_title, g_level FROM '.Conf::$DB['PREFIX'].'groupes '
        .$limiters_text.' ORDER BY gID DESC '.$limit, $args) OR Site::$DB->trigger(__LINE__, __FILE__);
        
        $data = array();
        $count = 0;
        $results = $query->fetchAll();
        foreach($results as $result)
        {
            $data[] = new Group($result);
            ++$count;
        }
        
        $return = array($count, (!$force_array AND $count == 1 AND isset($limiters['ID'])) ? $data[0] : $data);
        
        $query->closeCursor();
        
        return $return;
    }
    
    public static function count(array $limiters=array())
    {
        list($limit, $limiters_text, $args) = parent::getLimiters($limiters, self::$valid_limiters);
        $limiters_text = (!empty($limiters_text)) ? 'WHERE '.$limiters_text : '';
        
        $query = Site::$DB->query('SELECT COUNT(*) as count FROM '.Conf::$DB['PREFIX'].'groupes '
        .$limiters_text.' '.$limit, $args) OR Site::$DB->trigger(__LINE__, __FILE__);
        
        $dn = $query->fetchAll();
        
        $query->closeCursor();
        
        return $dn[0]['count'];
    }
}
