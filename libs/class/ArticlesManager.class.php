<?php
if(!defined('IN') OR IN !== True)
    die( 'fatal error :: you can\'t access this page directly !' );

final class ArticlesManager {
    
    private static $valid_limiters = array(
                                    'ID'       => 'a.aID = ?',
                                    'LIMIT'    => 'LIMIT %u, %u',
                                    'VALIDE'   => 'a.valide = ?',
                                    'USER'     => 'a.m_ID = ?',
                                    'CAT_ID'   => 'a.c_ID = ?',
                                    'CAT_SLUG' => 'c.slug = ?',
                                    'ORDER_BY' => 'ORDER BY %s %s',
                                    'LIKE'     => 'a.titre LIKE ? OR a.contenu LIKE ?',
                                );
    
    private static $order_by = array(
                                'ID'     => 'a.aID',
                                'VALIDE' => 'a.valide',
                                'DATE'   => 'a.date',
                                'USER'   => 'a.m_ID',
                                'CAT_ID' => 'a.c_ID'
                            );
    
    
    public static function get(array $limiters=array(), $force_array=False)
    {
        list($limit, $order_by, $limiters_text, $args) = self::getLimiters($limiters);
        $limiters_text = (!empty($limiters_text)) ? 'WHERE '.$limiters_text : '';
        
        $query = Site::$DB->query('SELECT a.aID, a.m_ID, a.c_ID, a.titre, a.contenu, a.date, '
        .'DATE_FORMAT(a.date, \'%e/%c/%Y à %k:%i\') as date_fmt, '
        .'a.nb_comments, a.valide, c.cID, c.slug, c.nom as cat_name, m.mID, m.m_pseudo, '
        .'m.m_nom, m.m_prenom FROM '.Conf::$DB['PREFIX'].'articles a '
        .'LEFT JOIN '.Conf::$DB['PREFIX'].'categories c ON c.cID = a.c_ID '
        .'LEFT JOIN '.Conf::$DB['PREFIX'].'membres m ON m.mID = a.m_ID '
        .$limiters_text.' '.$order_by.' '.$limit, $args) OR Site::$DB->trigger(__LINE__, __FILE__);
        
        $data = array();
        $count = 0;
        $results = $query->fetchAll();
        foreach($results as $result)
        {
            $data[] = new Article($result);
            ++$count;
        }
        
        $return = array($count, (!$force_array AND $count == 1 AND isset($limiters['ID'])) ? $data[0] : $data);
        
        $query->closeCursor();
        
        return $return;
    }
    
    public static function count(array $limiters=array())
    {
        list($limit, $order_by, $limiters, $args) = self::getLimiters($limiters);
        $limiters = (!empty($limiters)) ? 'WHERE '.$limiters : '';
        
        $query = Site::$DB->query('SELECT COUNT(*) as count FROM '.Conf::$DB['PREFIX'].'articles a '
        .'LEFT JOIN '.Conf::$DB['PREFIX'].'categories c ON c.cID = a.c_ID '
        .$limiters.' '.$limit, $args) OR Site::$DB->trigger(__LINE__, __FILE__);
        
        $dn = $query->fetchAll();
        
        $query->closeCursor();
        
        return $dn[0]['count'];
    }
    
    public static function save(Article &$item)
    {
        return (is_null($item->aID)) ? self::newArticle($item) : self::update($item);
    }
    
    public static function incrNbComs($aID)
    {
        $query = Site::$DB->query('UPDATE '.Conf::$DB['PREFIX'].'articles SET nb_comments = nb_comments +1 WHERE aID = ?',
                                  array($aID)) OR Site::$DB->trigger(__LINE__, __FILE__);
        
        return ($query->rowCount() == 1);
    }
    
    public static function setNbComs($aID)
    {
        $count = CommentairesManager::count(array('ARTICLE' => $aID, 'VALIDE' => 1));
        
        $query = Site::$DB->query('UPDATE '.Conf::$DB['PREFIX'].'articles SET nb_comments = ? WHERE aID = ?',
                         array($count, $aID)) OR Site::$DB->trigger(__LINE__, __FILE__);
        
        return ($query->rowCount() == 1);
    }
    
    private static function newArticle(Article &$item)
    {
        $query = Site::$DB->query('INSERT INTO '.Conf::$DB['PREFIX'].'articles '
            .'(m_ID, c_ID, titre, contenu, date) VALUES (?, ?, ?, ?, NOW())',
            array($item->m_ID, $item->c_ID, $item->titre, $item->contenu))
        OR Site::$DB->trigger(__LINE__, __FILE__);
        
        return ($query->rowCount() == 1);
    }
    
    private static function update(Article &$item)
    {
        $keys = $item->getKeys();
        
        $update = $args = array();
        foreach($keys as $key)
        {
            if(!in_array($key, array('cID', 'mID', 'm_pseudo', 'slug', 'm_nom', 'cat_name', 'm_prenom', 'date_fmt')))
            {
                $update[] = $key.' = ?';
                $args[] = $item->{$key};
            }
        }
        
        $query = Site::$DB->query('UPDATE '.Conf::$DB['PREFIX'].'articles SET '.implode(', ', $update).' WHERE aID = '.(int) $item->aID, $args) OR Site::$DB->trigger(__LINE__, __FILE__);
        
        return ($query->rowCount() == 1);
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
                elseif($item == 'LIKE')
                {
                    $return[] = self::$valid_limiters[$item];
                    $args[] = '%'.$values.'%';
                    $args[] = '%'.$values.'%';
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
