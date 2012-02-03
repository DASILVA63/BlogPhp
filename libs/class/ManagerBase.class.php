<?php
if(!defined('IN') OR IN !== True)
    die('fatal error :: you can\'t access this page directly !');

abstract class ManagerBase
{
    protected static function getLimiters(array $limiters, $valid_limiters)
    {
        $return = $args = array();
        $limit = '';
        foreach($limiters as $item => $value)
        {
            if(!empty($valid_limiters[$item]))
            {
                if($item == 'LIMIT')
                    $limit = vsprintf($valid_limiters['LIMIT'], $value);
                else
                {
                    $return[] = $valid_limiters[$item];
                    $args[] = $value;
                }
            }
        }
        
        return array($limit, (!empty($return)) ? implode(' AND ', $return) : '', $args);
    }
}
