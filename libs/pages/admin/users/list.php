<?php
if(!defined('IN') OR IN !== True)
    die('fatal error :: you can\'t access this page directly !');


require_once Conf::$DIRS['PAGES'].'admin/AdminPageBase.php';


final class Page extends AdminPageBase
{
    public function main()
    {
        list($nb_results, $dn) = UserManager::get();

        foreach($dn as $item)
            $item->parse('user');
        
        Site::setTPLFile('admin/list_users.html');
        Site::setTitle('Gérer les utilisateurs');
    }
}
