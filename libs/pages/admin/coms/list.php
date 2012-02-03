<?php
if(!defined('IN') OR IN !== True)
    die('fatal error :: you can\'t access this page directly !');


require_once Conf::$DIRS['PAGES'].'admin/AdminPageBase.php';


final class Page extends AdminPageBase
{
    public function main()
    {
        list($nb_results, $dn) = CommentairesManager::get(array('ORDER_BY' => array('DATE', 'DESC')));

        foreach($dn as $item)
            $item->parse();
        
        Site::setTPLFile('admin/list_coms.html');
        Site::setTitle('Gérer les commentaires');
    }
}
