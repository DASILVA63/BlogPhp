<?php
if(!defined('IN') OR IN !== True)
    die('fatal error :: you can\'t access this page directly !');

class Article extends ObjectBase {
    
    public function __set($key, $value)
    {
        if($key == 'aID') return; // on ne modifie pas l'id de la vente
        
        $this->dn[$key] = $value;
    }
    
    public function parse($bloc_name = '')
    {
        $data = array_change_key_case($this->dn, CASE_UPPER);
        $contenu = $data['CONTENU'];
        unset($data['CONTENU']);
        
        $data = array_map('htmlspecialchars', $data);
        
        $data['F_DATE'] = $data['DATE_FMT'];
        $data['F_CONTENU'] = $contenu;
        
        $data['C_NOM'] = ucfirst($data['CAT_NAME']);
        $data['C_SLUG'] = (!empty($data['SLUG'])) ? $data['SLUG'] : $data['CID'];
        
        if(!empty($bloc_name))
            Site::$TPL->setBlock($bloc_name, $data);
        else
            Site::$TPL->set($data);
    }
}
?>
