<?php
if(!defined('IN') OR IN !== True)
    die('fatal error :: you can\'t access this page directly !');


abstract class AdminPageBase
{
    protected function main()
    {
        if(!Site::$User->isLoggedIn() OR !Site::$User->isAdmin())
            Site::redirect(Conf::$SITE['URL'], 'Vous n\'avez pas le droit de voir cette page');
    }
}
