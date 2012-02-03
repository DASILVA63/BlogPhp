<?php
if(!defined('IN') OR IN !== True)
    die('fatal error :: you can\'t access this page directly !');

class Commentaire extends ObjectBase {
    
    public function __set($key, $value)
    {
        if($key == 'rID') return; // on ne modifie pas l'id de la vente
        
        $this->dn[$key] = $value;
    }
    
    public function parse($bloc_name = 'com')
    {
        $data = array_change_key_case($this->dn, CASE_UPPER);
        $contenu = nl2br($data['COMMENTAIRE']);
        unset($data['CONTENU']);
        
        $data = array_map('htmlspecialchars', $data);
        
        $data['F_DATE'] = $data['DATE_FMT'];
        $data['F_COMMENTAIRE'] = $contenu;
        
        $data['F_AUTEUR'] = ucfirst($data['AUTEUR']);
        
        if(!empty($bloc_name))
            Site::$TPL->setBlock($bloc_name, $data);
        else
            Site::$TPL->set($data);
    }
}
?>
