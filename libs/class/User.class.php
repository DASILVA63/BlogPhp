<?php
if(!defined('IN') OR IN !== True)
    die('fatal error :: you can\'t access this page directly !');

class User extends ObjectBase {
    
    public function isLoggedIn()
    {
        return (!empty($this->dn['mID']) AND !empty($_SESSION['mID']) AND $_SESSION['mID'] == $this->dn['mID']);
    }
    
    public function isAdmin()
    {
        return (!empty($this->dn['g_ID']) AND $this->dn['g_ID'] == 1);
    }
    
    public function setPassword($pass)
    {
        $this->dn['m_pass'] = sha1($pass);
    }
    
    public function __set($key, $value)
    {
        if($key == 'mID') return;
        
        $this->dn[$key] = $value;
    }
    
    public function parse($bloc_name='user')
    {
        $data = array_change_key_case($this->dn, CASE_UPPER);
        $data = array_map('htmlspecialchars', $data);
        
        $data['F_DATE'] = $data['DATE_FMT'];
        
        if(!empty($bloc_name))
            Site::$TPL->setBlock($bloc_name, $data);
        else
            Site::$TPL->set($data);
    }
}
?>
