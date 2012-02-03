<?php
if(!defined('IN') OR IN !== True)
    die('fatal error :: you can\'t access this page directly !');


require_once Conf::$DIRS['PAGES'].'admin/formAdminPageBase.php';


final class Page extends FormAdminPageBase
{
    public function main()
    {
        $this->id = (!empty($_GET['id']) AND is_numeric($_GET['id']) AND $_GET['id'] > 0) ? (int) $_GET['id'] : False;
        
        $this->showForm();
        
        if($this->id === False)
            Site::setTitle('Ajouter un utilisateur');
        else
        {
            $this->bindData();
            Site::setTitle('Modifier un utilisateur');
        }
        
        parent::main();
    }
    
    protected function showForm()
    {
        list($nb_results, $dn_groupes) = GroupsManager::get();
        $groupes = array();
        foreach($dn_groupes as $groupe)
            $groupes[$groupe->gID] = $groupe->g_title;
        
        $pass_message = ($this->id !== False) ? '(laissez vide pour ne pas changer)' : '';
        
        $this->form = new Form();
        $this->form->add('Text', 'm_pseudo')->setLabel('Pseudo');
        $this->form->add('Password', 'm_pass')->setLabel('Mot de Passe');
        $this->form->add('Password', 'pass_confirm')->setLabel('Confirmation');
        $this->form->add('Text', 'm_nom')->setLabel('Nom ');
		$this->form->add('Text', 'm_prenom')->setLabel('Prénom ');        
        $this->form->add('Email', 'm_mail')->setLabel('Mail');
        $this->form->add('Select', 'g_ID')->setLabel('Groupe')->options($groupes);
        $this->form->add('Bool', 'm_activated', True)->setLabel('Valide')->required(False);
        $this->form->add('SubmitButton', 'Envoyer')->addClass('button');
    }
    
    protected function processForm()
    {
        if(!parent::processForm())
            return;
        
        $data = $this->form->get();
        unset($data['Envoyer']);

        #mise à jour
        if($this->id)
        {
            $pass = $this->form->get('m_pass', '');
            $confirm = $this->form->get('pass_confirm', '');
            
            $this->dn->updateData($data);
            
            if(!empty($pass))
            {
                if($pass == $confirm)
                    $this->dn->setPassword($pass);
                else
                    $this->form->trigger_error('pass_confirm', 'Les mots de passe entrés ne sont pas identiques');
            }
            else
                unset($data['m_pass']);
        }
        else
        {
			
			$this->dn = new User($data);
			$this->dn->setPassword($data['m_pass']);
			
        }

        if($this->form->isValid())
        {
            if(UserManager::save($this->dn))
                Site::redirect('./admin/users/edit/'.(($this->id) ? $this->id : Site::$DB->lastInsertId()).'/', 'Utilisateur sauvegardé !');
            else
                Site::redirect('./admin/users/new/', 'Erreur lors de la sauvegarde de l\'utilisateur.');
        }
    }
    
    private function bindData()
    {
        list($nb_results, $this->dn) = UserManager::get(array('ID' => $this->id));
        
        if($nb_results != 1)
            Site::redirect('./admin/users/', 'Cet utilisateur n\'existe pas ou a été supprimé');
        
        $this->form->bound($this->dn->asArray());
        
        $this->form->field('m_pass')->required(False); 
        $this->form->field('pass_confirm')->required(False); 
    }
}
