<?php
if(!defined('IN') OR IN !== True)
    die('fatal error :: you can\'t access this page directly !');


require_once Conf::$DIRS['PAGES'].'formPageBase.php';


final class Page extends FormPageBase
{
    public function main()
    {
        if(Site::$User->isLoggedIn() AND !isset($_GET['deconnexion']))
            Site::redirect(Conf::$SITE['URL'], 'Vous êtes déjà connecté');
        
        //Déconnexion demandée ?
        if(isset($_GET['deconnexion']) AND UserManager::deconnect())
            Site::redirect(Conf::$SITE['URL'], 'Vous êtes désormais déconnecté');
        
        $this->showForm();
    }
    
    protected function showForm()
    {
        Site::setTitle('Connexion');
        
        //création du formulaire
        $this->form = new Form();
        $this->form->add('Text', 'pseudo')->setLabel('Pseudo');
        $this->form->add('Password', 'pass')->setLabel('Mot de passe');
        $this->form->add('SubmitButton', 'Connectez moi !')->addClass('button');
        
        parent::showForm();
    }
    
    protected function processForm()
    {
        if(!parent::processForm())
            return;
        
        list($nb_results, $dn_user) = UserManager::get(array(
                                                             'PSEUDO' => $_POST['pseudo'],
                                                             'PASS'   => sha1($_POST['pass'])
                                                            ), True);
        
        if($nb_results != 1)
            Site::redirect(Conf::$SITE['URL'].'connexion/', 'Vos identifiants sont incorrects !');
        
        if($dn_user->m_activated != 1)
            Site::redirect(Conf::$SITE['URL'].'connexion/', 'Ce compte n\'est pas encore activé.');
        
        //les vérifs ont été faites, on connecte l'user
        UserManager::connect($dn_user);
        
        Site::redirect(Conf::$SITE['URL'], 'Vous êtes désormais connecté en tant que <strong>'.Site::$User->m_pseudo.'</strong>');
    }
}
