<?php
if(!defined('IN') OR IN !== True)
    die('fatal error :: you can\'t access this page directly !');

class Group extends ObjectBase {
    
    public function __set($key, $value)
    {
        if($key == 'gID') return; // on ne modifie pas l'id du groupe
        
        $this->dn[$key] = $value;
    }
    
    public function parse($bloc_name = 'group')
    {
        $data = array_change_key_case_r($this->dn);
        
        array_map_r('htmlspecialchars', $data);
        if(!empty($bloc_name))
            Site::$tpl->assign_block_vars($bloc_name, $data);
        else
            Site::$tpl->set($data);
    }
}
?>
