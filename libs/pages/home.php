<?php
if(!defined('IN') OR IN !== True)
    die('fatal error :: you can\'t access this page directly !');


require_once Conf::$DIRS['PAGES'].'formPageBase.php';


final class Page extends FormPageBase
{
    private $limiters = array(
                            'VALIDE'  => 1,
                            'ORDER_BY' => array('DATE', 'DESC')
                        );
    
    public function main()
    {
        Site::$TPL->set(array(
                        'NB_COMS' => 0,
                        'RECHERCHE' => (!empty($_GET['recherche'])) ? htmlentities($_GET['recherche']) : '',
                    ));
        
        $this->getLimiters();
        
        if(!isset($this->limiters['ID']))
            $this->showArticlesPagination();
        else
        {
            $this->showForm();
            $this->getComs();
        }
        
        //récupération et préparation des articles
        list($nb_results, $dn_articles) = ArticlesManager::get($this->limiters, True);
        
        if($nb_results == 0)
            Site::redirect(Conf::$SITE['URL'], 'Erreur, aucun article ne correspond à vos critères');
        
        //affichage
        foreach($dn_articles as $article)
            $article->parse('article');
        
        Site::setTPLFile('home.html');
        
        //définition du titre de la page
        if(isset($this->limiters['ID']))
            Site::setTitle($dn_articles[0]->titre);
        elseif(isset($this->limiters['CAT_ID']) OR isset($this->limiters['CAT_SLUG']))
            Site::setTitle($dn_articles[0]->cat_name);
        else
            Site::setTitle('Accueil');
    }
    
    protected function showForm()
    {
        $this->form = new Form();
        $this->form->add('Text', 'auteur')->setLabel('Nom');
        $this->form->add('Email', 'mail')->setLabel('Adresse Mail');
        $this->form->add('Textarea', 'commentaire')->setLabel('Message')
                                                   ->cols(40)->rows(9);
        $this->form->add('SubmitButton', 'Envoyer')->addClass('button');
        
        parent::showForm();
    }
    
    protected function processForm()
    {
        if(!parent::processForm())
            return;
        
        $_POST['a_ID'] = $this->limiters['ID'];
        $posted = CommentairesManager::save(new Commentaire($_POST));
        
        Site::redirect(Conf::$SITE['URL'].'?article='.$_POST['a_ID'], $posted !== False ? 'Votre commentaire a été posté !' : 'Erreur, le commentaire n\'a pas été sauvegardé');
    }
    
    private function getComs()
    {
        //récupération et préparation des commentaires
        list($nb_coms, $dn_coms) = CommentairesManager::get(array('ARTICLE' => $_GET['id'], 'VALIDE' => True));

        // affichage des commentaires
        foreach($dn_coms as $com)
            $com->parse();
        
        Site::$TPL->set(array(
                            'NB_COMS' => $nb_coms,
                            'SINGLE_ARTICLE' => True,
                        ));
    }
    
    private function showArticlesPagination()
    {
        //définition de la pagination
        $nb_pages = ceil(ArticlesManager::count($this->limiters) / 5);
        $page = (!empty($_GET['p']) AND is_numeric($_GET['p']) AND $_GET['p'] >= 1) ? ($_GET['p'] > $nb_pages) ? $nb_pages : (int) $_GET['p'] : 1;
        
        $this->limiters['LIMIT'] = array(($page - 1) * 5, 5);
        
        $pagination = get_list_page($page, $nb_pages);
        foreach($pagination as $page)
            Site::$TPL->setBlock('pagination', array('PAGE_NUM' => $page));
    }
    
    private function getLimiters()
    {
        //si demandé, on filtre selon la catégorie
        if(!empty($_GET['slug']))
        {
            if(is_numeric($_GET['slug']) AND $_GET['slug'] > 0)
                $this->limiters['CAT_ID'] = (int) $_GET['slug'];
            else
                $this->limiters['CAT_SLUG'] = $_GET['slug'];
        }
        
        //en cas de recherche
        if(!empty($_GET['recherche']))
            $this->limiters['LIKE'] = $_GET['recherche'];
        
        //si demandé, on filtre selon l'id de l'article
        if(!empty($_GET['id']) AND is_numeric($_GET['id']) AND $_GET['id'] > 0)
            $this->limiters['ID'] = (int) $_GET['id'];
    }
}
