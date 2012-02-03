<?php
if(!defined('IN') OR IN !== True)
    die('fatal error :: you can\'t access this page directly !');


abstract class FormPageBase
{
    protected $form = null;
    
    protected function main()
    {
        $this->showForm();
    }
    
    protected function showForm()
    {
        Site::setTPLFile('form.html');
        
        if($_SERVER['REQUEST_METHOD'] == 'POST')
            $this->processForm();
        
        Site::$TPL->set('FORM', $this->form->asP());
    }
    
    protected function processForm()
    {
        $this->form->bound(array_map('trim', $_POST));
        
        if($this->form->isValid())
            return True;
        else
        {
            $this->displayErrors();
            return False;
        }
    }
    
    protected function displayErrors()
    {
        foreach($this->form->getErrors() as $error)
        {
            Site::$TPL->set_block('message', array(
                                                    'ERREUR'      => $error['ERROR_TEXT'],
                                                    'FIELD_LABEL' => $error['FIELD_LABEL']
                                                )
                                );
            Site::$TPL->set('ERREUR', True);
        }
    }
}
