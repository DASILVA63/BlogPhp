<?php
if(!defined('IN') OR IN !== True)
    die('fatal error :: you can\'t access this page directly !');


require_once Conf::$DIRS['PAGES'].'admin/formAdminPageBase.php';


final class Page extends FormAdminPageBase
{
    public function main()
    {
        Site::addJavascript(array(
                                array('tiny_mce/tiny_mce.js', 20),
                                array('tiny_mce/launch_admin_article.js', 20),
                            ));
        
        $this->id = (!empty($_GET['id']) AND is_numeric($_GET['id']) AND $_GET['id'] > 0) ? (int) $_GET['id'] : False;
        
        $this->showForm();
        
        if($this->id === False)
            Site::redirect('./admin/commentaires/');
        
        $this->bindData();
        Site::setTitle('Éditer un commentaire');
        
        parent::main();
    }
    
    protected function showForm()
    {
        $this->form = new Form();
        $this->form->add('Text', 'auteur')->setLabel('Nom');
        $this->form->add('Email', 'mail')->setLabel('Adresse Mail');
        $this->form->add('Textarea', 'commentaire')->setLabel('Message')->cols(40)->rows(9);
        $this->form->add('Bool', 'valide', True)->setLabel('Valide')->required(False);
        $this->form->add('SubmitButton', 'Envoyer')->addClass('button');
    }
    
    protected function processForm()
    {
        if(!parent::processForm())
            return;
        
        $data = $this->form->get();
        unset($data['Envoyer']);

        #mise à jour
        $this->dn->updateData($data);

        if(CommentairesManager::save($this->dn))
            Site::redirect('./admin/commentaires/edit/'.$this->id.'/', 'Commentaire sauvegardé !');
        else
            Site::redirect('./admin/commentaires/', 'Erreur lors de la sauvegarde du commentaire.');
    }
    
    private function bindData()
    {
        list($nb_results, $this->dn) = CommentairesManager::get(array('ID' => $this->id));
        
        if($nb_results != 1)
            Site::redirect('./admin/commentaires/', 'Ce commentaire n\'existe pas ou a été supprimé');
        
        $this->form->bound($this->dn->asArray());
    }
}
