<?php
if(!defined('IN') OR IN !== True)
    die('fatal error :: you can\'t access this page directly !');


require_once Conf::$DIRS['PAGES'].'admin/AdminPageBase.php';


final class Page extends AdminPageBase
{
    public function main()
    {
        parent::main();
        
        Site::setTPLFile('admin/home.html');
        Site::setTitle('Administration');
        Site::$TPL->set(array(
                        'NB_ARTICLES' => ArticlesManager::count(),
                        'NB_COMS' => CommentairesManager::count(),
                        'NB_USERS' => UserManager::count(),
                    ));
    }
}
