<?php

if(!defined('IN') OR IN !== True)
    die('fatal error :: you can\'t access this page directly !');


require_once Conf::$DIRS['PAGES'].'formPageBase.php';


final class Page extends FormPageBase
{
    public function main()
    {
        $this->showForm();
        
        Site::setTitle('Contact');
    }
    
    protected function showForm()
    {
        $this->form = new Form();
        $this->form->add('Text', 'pseudo')->setLabel('Nom');
        $this->form->add('Email', 'mail')->setLabel('Adresse Mail');
        $this->form->add('Text', 'sujet')->setLabel('Sujet');
        $this->form->add('Textarea', 'message')->setLabel('Message')->cols('54%')->rows(7)->setMinLength(10);
        $this->form->add('Text', 'must_fill')->setID('must_fill')->required(False); // pour "anti-spam"
        $this->form->add('SubmitButton', 'Envoyer')->addClass('button');
        
        parent::showForm();
    }
    
    protected function processForm()
    {
        if(!parent::processForm())
            return;	
        
        $_POST['pseudo'] = htmlspecialchars($_POST['pseudo']);
        $_POST['mail'] = htmlspecialchars($_POST['mail']);
        $_POST['message'] = nl2br(htmlspecialchars($_POST['message']));
		$_exp = Conf::$SITE['CONTACT_MAIL'] ;
        $message_mail = <<<eof
Un message a été envoyé à partir de « BlogPHP »,<br />
<br /><hr />
-----------------------------------------------<br />
Pseudo :: $_POST[pseudo]<br />
Mail :: $_exp <br />
Sujet :: $_POST[sujet]<br />
-----------------------------------------------<br /><br />
Message :: $_POST[message] <br />
<br /><hr />
eof;
        
        envoi_mail($_POST['mail'], Conf::$SITE['TITRE'].' :: Contact :: '.$_POST['sujet'], $message_mail);
        Site::redirect(Conf::$SITE['URL'], 'Votre message a été envoyé à l\'équipe.', 4);
    }
}
