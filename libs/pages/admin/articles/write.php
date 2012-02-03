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
            Site::setTitle('Rédiger un article');
        else
        {
            $this->bindData();
            Site::setTitle('Modifier un article');
        }
        
        parent::main();
    }
    
    protected function showForm()
    {
//        list($nb_results, $dn_categories) = CategoriesManager::get();
//        $categories = array();
//        foreach($dn_categories as $categorie)
//            $categories[$categorie->cID] = $categorie->nom;
        
        $this->form = new Form();
        $this->form->add('Text', 'titre')->setLabel('Titre');
//        $this->form->add('Select', 'c_ID')->setLabel('Catégorie')->options($categories);
        $this->form->add('Textarea', 'contenu')->setLabel('Article')->cols('75%')->rows(10);
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
        if($this->id)
            $this->dn_article->updateData($data);
        #nouvel article
        else
        {
            $this->dn_article = new Article($data);
            $this->dn_article->m_ID = Site::$User->mID;
        }

        if(ArticlesManager::save($this->dn_article))
            Site::redirect('./admin/articles/edit/'.(($this->id) ? $this->id : Site::$DB->lastInsertId()).'/', 'Article sauvegardé !');
        else
            Site::redirect('./admin/articles/new/', 'Erreur lors de la sauvegarde de l\'article.');
    }
    
    private function bindData()
    {
        list($nb_results, $this->dn_article) = ArticlesManager::get(array('ID' => $this->id));

        if($nb_results != 1)
            Site::redirect('./admin/articles/', 'Cet article n\'existe pas ou a été supprimé');

        $this->form->bound($this->dn_article->asArray());
    }
}
