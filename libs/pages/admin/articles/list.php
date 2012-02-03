<?php
if(!defined('IN') OR IN !== True)
    die('fatal error :: you can\'t access this page directly !');


require_once Conf::$DIRS['PAGES'].'admin/AdminPageBase.php';


final class Page extends AdminPageBase
{
    public function main()
    {
        list($nb_results, $dn_articles) = ArticlesManager::get(array('ORDER_BY' => array('DATE', 'DESC')));

        foreach($dn_articles as $item)
            $item->parse('article');
        
        Site::setTPLFile('admin/list_articles.html');
        Site::setTitle('Gérer les articles');
    }
}
