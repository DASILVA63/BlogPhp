<?php
if(!defined('IN') OR IN !== True)
    die( 'fatal error :: you can\'t access this page directly !' );

final class CommentairesManager {
    
    private static $valid_limiters = array(
                                    'ID'       => 'c.rID = ?',
                                    'ARTICLE'  => 'c.a_ID = ?',
                                    'LIMIT'    => 'LIMIT %u, %u',
                                    'VALIDE'   => 'c.valide = ?',
                                    //'DATE'     => 'date >= %u',
                                    'ORDER_BY' => 'ORDER BY %s %s',
                                );
    
    private static $order_by = array(
                                'ID'     => 'c.rID',
                                'DATE'   => 'c.date'
                            );
    
    
    public static function get(array $limiters=array(), $force_array=False)
    {
        list($limit, $order_by, $limiters_text, $args) = self::getLimiters($limiters);
        $limiters_text = (!empty($limiters_text)) ? 'WHERE '.$limiters_text : '';
        
        $query = Site::$DB->query('SELECT c.rID, c.a_ID, c.auteur, c.mail, '
        .'c.commentaire, c.date, c.valide, a.titre, '
        .'DATE_FORMAT(c.date, \'%e/%c/%Y à %k:%i\') as date_fmt '
        .'FROM '.Conf::$DB['PREFIX'].'commentaires c '
        .'INNER JOIN '.Conf::$DB['PREFIX'].'articles a ON a.aID = c.a_ID '
        .$limiters_text.' '.$order_by.' '.$limit, $args) OR Site::$DB->trigger(__LINE__, __FILE__);
        
        $data = array();
        $count = 0;
        $results = $query->fetchAll();
        foreach($results as $result)
        {
            $data[] = new Commentaire($result);
            ++$count;
        }
        
        $return = array($count, (!$force_array AND $count == 1 AND isset($limiters['ID'])) ? $data[0] : $data);
        
        $query->closeCursor();
        
        return $return;
    }
    
    public static function count(array $limiters=array())
    {
        list($limit, $order_by, $limiters_text, $args) = self::getLimiters($limiters);
        $limiters_text = (!empty($limiters_text)) ? 'WHERE '.$limiters_text : '';
        
        $query = Site::$DB->query('SELECT COUNT(*) as count FROM '.Conf::$DB['PREFIX'].'commentaires c '
        .$limiters_text.' '.$order_by.' '.$limit, $args) OR Site::$DB->trigger(__LINE__, __FILE__);
        
        $dn = $query->fetchAll();
        
        $query->closeCursor();
        
        return $dn[0]['count'];
    }
    
    public static function save(Commentaire &$item)
    {
        return (is_null($item->rID)) ? self::new_com($item) : self::update($item);
    }
    
    private static function new_com(Commentaire &$item)
    {
        $query = Site::$DB->query('INSERT INTO '.Conf::$DB['PREFIX'].'commentaires '
        .'(a_ID, auteur, mail, commentaire, date) VALUES (?, ?, ?, ?, NOW())',
        array($item->a_ID, $item->auteur, $item->mail, $item->commentaire))
        OR Site::$DB->trigger(__LINE__, __FILE__);
        
        $id = Site::$DB->lastInsertId();
        
        ArticlesManager::incrNbComs($item->a_ID);
        
        return ($query->rowCount() == 1) ? $id : False;
    }
    
    private static function update(Commentaire &$item)
    {
        $keys = $item->getKeys();
        
        $s_update = array();
        foreach($keys as $key)
        {
            if(!in_array($key, array('titre', 'aID')))
            {
                $s_update[] = $key.' = ?';
                $args[] = $item->{$key};
            }
        }
        
        $args[] = $item->rID;
        
        Site::$DB->query('UPDATE '.Conf::$DB['PREFIX'].'commentaires SET '.
                         implode(', ', $s_update).' WHERE rID = ?', $args) OR Site::$DB->trigger(__LINE__, __FILE__);
        
        ArticlesManager::setNbComs($item->a_ID);
        
        return True;
    }
    
    private static function getLimiters(array $limiters)
    {
        $return = $args = array();
        $limit = $order_by = '';
        foreach($limiters as $item => $values)
        {
            if(!empty(self::$valid_limiters[$item]))
            {
                if($item == 'LIMIT')
                {
                    $limit = vsprintf(self::$valid_limiters['LIMIT'], $values);
                }
                elseif($item == 'ORDER_BY')
                {
                    $values[0] = (!empty($values[0]) AND array_key_exists($values[0], self::$order_by)) ? self::$order_by[$values[0]] : self::$order_by['DATE'];
                    $values[1] = (!empty($values[1]) AND (strtoupper($values[1]) == 'DESC' OR strtoupper($values[1]) == 'ASC')) ? strtoupper($values[1]) : 'DESC';
                    
                    $order_by = vsprintf(self::$valid_limiters['ORDER_BY'], $values);
                }
                else
                {
                    $return[] = self::$valid_limiters[$item];
                    $args[] = $values;
                }
            }
        }
        
        return array($limit, $order_by, (!empty($return)) ? implode(' AND ', $return) : '', $args);
    }
}
?>
