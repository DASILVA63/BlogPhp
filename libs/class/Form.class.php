<?php

/**
*
*   Class de création de formulaires
*
**/
class Form {

    protected $attrs;
    protected $label_suffix = ' : ';
    protected $values = array();
    protected $fields = array();
    protected $errors_msg = array();

    public function __construct($method='POST', $action='')
    {
        $this->attrs = new AttributeList(array(
                                            'method' => $method,
                                            'action' => $action
                                            )
                                        );
    }

    /**
    *   Ajouter un champ
    *
    *   string $type :: type de champ à ajouter (correspond au nom de l'objet réprésentant le champ)
    *   string $name :: nom du champ (doit être unique)
    *   string $value :: valeur par défaut
    *
    *   return obj (retourne l'objet représentant le champ créé)
    **/
    public function add($type, $name, $value='', $label='')
    {
        if(isset($this->fields[$name]))
            trigger_error('Un champ nommé « '.$name.' » existe déjà.', E_USER_WARNING);
        else
        {
            $field = 'Field_'.$type;

            if($field == 'Field_SubmitButton')
                $o_Field = new $field($name);
            else
                $o_Field = new $field($name, $value, $label);

            $this->fields[$name] = $o_Field;

            return $o_Field;
        }
    }

    /**
    *   Remplis les champs avec l'array fourni
    *
    *   array $data :: ('clef' => 'valeur') où clef == nom du champ
    *
    *   return void
    **/
    public function bound(array &$data)
    {
        foreach($this->fields as $name => $field)
        {
            if($field->getType() != 'submit')
                $field->setValue((isset($data[$name])) ? $data[$name] : Null);
        }
    }

    /**
    *   Indique si les champs du formulaire ont été correctement remplis
    *   (méthode à appeler si on veut générer les erreurs)
    *
    *   return bool
    **/
    public function isValid()
    {
        $valid = True;
        foreach($this->fields as $name => $field)
        {
            if(!$field->isValid())
            {
                $valid = False;
                foreach($field->getErrors() as $error)
                    $this->trigger_error($field, $error);
            }

            $this->values[$field->getName()] = $field->getValue();
        }

        return ($valid AND count($this->errors_msg) == 0);
    }

    /**
    *   Ajoute un message d'erreur à la liste des erreurs
    *
    *   string|obj $field :: nom du champ ou objet représentant le champ lié à l'erreur
    *   string $error_text :: message d'erreur à afficher
    *
    *   return void
    **/
    public function trigger_error($field, $error_text)
    {
        $oField = (is_string($field)) ? $this->fields[$field] : $field;

        $this->errors_msg[] = array(
                                    'ERROR_TEXT' => $error_text,
                                    'FIELD_LABEL' => $oField->getLabel()
                                );
    }

    /**
    *   Récupère l'objet représentant le champ ayant pour code $field_name
    *
    *   string $field_name :: nom du champ
    *
    *   return obj
    **/
    public function field($field_name)
    {
        if(isset($this->fields[$field_name]))
            return $this->fields[$field_name];
        else
            trigger_error('Aucun champ nommé « '.$field_name.' » n\'a été créé.', E_USER_WARNING);
    }

    /**
    *   Retourne une valeur du formulaire (ou toute si pas de clef fournie)
    *   si la clef ne correspond pas à un champ et qu'une valeur par défaut est donnée, on la renvoie
    *
    *   ATTENTION :: ne fonctionne qu'après un appel à la méthode isValid() !
    *
    *   string $var :: nom d'un item du formulaire dont on veut la valeur
    *   mixed $default :: valeur à renvoyer si pas de correspondance avec un champ
    *
    *   return mixed
    **/
    public function get($val='', $default='|NoDefaultValue|')
    {
        if(empty($val))
            return $this->values;
        elseif(isset($this->values[$val]))
            return $this->values[$val];
        elseif($default != '|NoDefaultValue|')
            return $default;
        else
            trigger_error('Aucun champ nommé « '.$val.' » n\'a été créé, il est donc impossible de récupérer sa valeur.', E_USER_WARNING);
    }

    /**
    *   Retourne un array contenant les messages d'erreur
    *   array de la forme ::
    *   array(
    *           'ERROR_TEXT' => "message d'erreur",
    *           'FIELD_LABEL' => "label du champ"
    *       )
    *
    *   return array
    **/
    public function getErrors()
    {
        return $this->errors_msg;
    }

    /**
    *   Change l'enctype du formulaire
    *
    *   string $enctype :: multipart/form-data ou application/x-www-form-urlencoded
    *
    *   return obj (le formulaire en question)
    **/
    public function setEnctype($enctype)
    {
        $enctype = strtolower($enctype);
        if (in_array($enctype, array('multipart/form-data', 'application/x-www-form-urlencoded')))
            $this->attrs['enctype'] = $enctype;

        return $this;
    }
    
    /**
    *   Change l'ID du formulaire
    *
    *   string $text :: nouvelle valeur
    *
    *   return obj (le champ en question)
    **/
    public function setID($id)
    {
        $this->attrs['id'] = $id;
        
        return $this;
    }
    
    /**
    *   Retourne l'ID du champ
    *
    *   return string
    **/
    public function getID()
    {
        return $this->attrs['id'];
    }
    
    /**
    *   Texte à afficher après le label du champ
    *
    *   string $string :: nouvelle valeur
    *
    *   return obj (le champ en question)
    **/
    public function setLabelSuffix($string)
    {
        $this->label_suffix = $string;
        
        return $this;
    }
    
    /**
    *   Retourne le texte à afficher après le label du champ
    *
    *   return string
    **/
    public function getLabelSuffix()
    {
        return $this->label_suffix;
    }
    
    /**
    *   Retourne le form préparé pour que le moteur de TPL puisse l'exploiter
    *
    *   return array('nom_du_champ' => array('code_html', 'label', array('mess erreur 1', 'mess erreur 2')))
    **/
    public function toArray()
    {
        $output = array();
        foreach($this->fields as $name => $field)
        {
            $output[$name] = array(
                                    'HTML'   => (string) $field, //on caste en string pour récupérer le code html
                                    'LABEL'  => $field->getLabel(),
                                    'ERRORS' => $field->getErrors(),
                                );
        }

        return $output;
    }

    /**
    *   Retourne le form au format HTML, prêt à être utilisé
    *   les champs sont encadrés par des <p>
    * 
    *   bool $display_errors :: dés/active l'affichage auto des erreurs
    *
    *   return string
    **/
    public function asP($display_errors=False)
    {
        $output = sprintf('<form %s>', $this->attrs);
        
        $errors = '<h3>Erreur</h3><ul class="form_error">';

        foreach($this->fields as $field)
        {
            if($field->getType() != 'hidden')
            {
                if($field->getErrors())
                    $errors .= '<li>'.$field->getLabel().$this->label_suffix.' '.implode(', ', $field->getErrors()).'</li>';
                
                $output .= "\n\t".'<p>';

                if($field->getLabel() != '')
                    $output .= "\n\t\t".'<label for="'.$field->getID().'">'.$field->getLabel().$this->label_suffix.'</label>';

                $output .= "\n\t\t".$field->toHTML();
                $output .= "\n\t".'</p>';
            }
            else
            {
                $output .= "\n\t".$field->toHTML();
            }
        }
        
        $errors .= '</ul>';

        $output .= "\n\r".'</form>';

        return ($display_errors) ? $errors.$output : $output;
    }

    public function __toString()
    {
        return $this->asP();
    }
}


/**
*
*   Class de base aux divers champs
*
**/
abstract class FormField {

    protected $class = array();
    protected $attrs;
    protected $label = '';
    protected $required = True;
    protected $auto_bound = True;
    protected $error_messages = array();
    protected $user_validation_rules = array();
    protected $errors_list = array(
        'required'  => 'Ce champ est obligatoire.',
    );
    

    public function __construct($name, $label='')
    {
        $this->attrs = new AttributeList(array('name' => $name, 'id' => 'id_'.$name));
        $this->attrs['value'] = '';
        $this->setLabel($label);
    }

    /**
    *   Dit si le contenu du champ est valide et crée les erreurs si besoin
    *   (pourra et devra être surchargée)
    *
    *   return bool
    **/
    public function isValid()
    {
        if($this->required AND empty($this->attrs['value']))
        {
            $this->_error('required');
            return False;
        }

        return True;
    }
    
    /**
    *   Ajoute une règle de validation
    *
    *   func|method $callback :: fonction/méthode de validation.
    *                            doit accepter un paramètre, et retourner le 
    *                            texte de l'erreur (si erreur), ou une chaine 
    *                            vide si tout est OK
    *
    *   return obj (le champ en question)
    **/
    public function addValidationRule($callback)
    {
        $this->user_validation_rules[(string) $callback] = $callback;
        
        return $this;
    }
    
    /**
    *   Indique si le formulaire passe les règles de validation personnalisées
    *
    *   return bool
    **/
    protected function passUserValidationRules()
    {
        foreach($this->user_validation_rules as $key => $rule)
        {
            $pass = call_user_func($rule, $this->getValue());
            
            if(!empty($pass))
            {
                $this->_userError($pass);
                return False;
            }
        }
        
        return True;
    }

    /**
    *   Rend obligatoire le champ ou pas
    *
    *   bool $bool :: status du champ
    *
    *   return obj (le champ en question)
    **/
    public function required($bool=True)
    {
        $this->required = (bool) $bool;

        return $this;
    }
    
    /**
    *   Dés/active un champ
    *
    *   bool $bool :: état d'activation
    *
    *   return obj (le champ en question)
    **/
    public function disabled($bool=True)
    {
        if((bool) $bool)
            $this->attrs['disabled'] = 'disabled';
        else
            unset($this->attrs['disabled']);

        return $this;
    }

    /**
    *   Dés/Active la remplissage auto lors de l'appel à la méthode bound()
    *   Empêche simplement la valeur de s'afficher, mais elle reste présente et récupérable via getValue() par ex
    *
    *   bool $bool :: état (True == activé)
    *
    *   return obj (le champ en question)
    **/
    public function autoBound($bool=True)
    {
        $this->auto_bound = (bool) $bool;

        return $this;
    }

    /**
    *   Donne l'état du remplissage auto
    *
    *   return bool
    **/
    public function canBound()
    {
        return $this->auto_bound;
    }

    /**
    *   Retourne le nom du champ
    *
    *   return string
    **/
    public function getName()
    {
        return $this->attrs['name'];
    }

    /**
    *   Retourne l'ID du champ
    *
    *   return string
    **/
    public function getID()
    {
        return $this->attrs['id'];
    }

    /**
    *   Retourne le type du champ
    *
    *   return string
    **/
    public function getType()
    {
        return (!empty($this->attrs['type'])) ? $this->attrs['type'] : '';
    }

    /**
    *   Retourne le contenu du label
    *
    *   return string
    **/
    public function getLabel()
    {
        return $this->label;
    }

    /**
    *   Retourne les messages d'erreurs
    *
    *   return array
    **/
    public function getErrors()
    {
        return array_values($this->error_messages);
    }

    /**
    *   Retourne la valeur du champ
    *
    *   return string
    **/
    public function getValue()
    {
        return $this->attrs['value'];
    }

    /**
    *   Retourne la valeur du champ (nettoyée) (à surcharger dans une class-fille)
    *
    *   return string
    **/
/*
    public function getCleanedValue($value='')
    {
        return (empty($value)) ? $this->attrs['value'] : $value;
    }
*/

    /**
    *   Ajoute une _class CSS au champ
    *
    *   string|array $class :: nom de la class
    *
    *   return obj (le champ en question)
    **/
    public function addClass($class)
    {
        if(is_array($class))
        {
            foreach($class as $item)
                $this->addClass($item);
        }
        else
        {
            if(!in_array($class, $this->class))
                $this->class[] = $class;
        }

        return $this;
    }

    /**
    *   Génère le contenu de l'attribut _class_ avec tous les éléments données
    *   méthode à appeler en début de __toString()
    *
    *   return void
    **/
    protected function _makeClass()
    {
        if(!empty($this->class))
            $this->attrs['class'] = implode(' ', $this->class);
    }

    /**
    *   Change la valeur d'un champ du formulaire
    *
    *   string $text :: nouvelle valeur
    *
    *   return obj (le champ en question)
    **/
    public function setValue($text)
    {
        $this->attrs['value'] = $text;

        return $this;
    }

    /**
    *   Change la valeur du label
    *
    *   string $text :: nouvelle valeur
    *
    *   return obj (le champ en question)
    **/
    public function setLabel($text)
    {
        $this->label = $text;

        return $this;
    }

    /**
    *   Change la valeur de l'ID du champ
    *
    *   string $text :: nouvelle valeur
    *
    *   return obj (le champ en question)
    **/
    public function setID($text)
    {
        $this->attrs['id'] = $text;

        return $this;
    }
    
    /**
    *   Permet de changer le message affiché lors d'une erreur
    *
    *   string $error_id :: id du message d'erreur
    *   string $text :: nouveau message
    *
    *   return obj (le champ en question)
    **/
    public function setErrorText($error_id, $text)
    {
        $this->errors_list[$error_id] = $text;
        
        return $this;
    }

    /**
    *   Ajoute le message d'erreur correspondant à $id dans la liste d'erreurs
    *
    *   string $id :: identifiant de l'erreur
    *
    *   return void
    **/
    protected function _error()
    {
        $args = func_get_args();
        $id = array_shift($args);
        
        $this->error_messages[$id] = vsprintf($this->errors_list[$id], $args);
    }
    
    
    /**
    *   Ajoute le message d'erreur dans la liste d'erreurs
    *   méthode utilisée pour les erreurs provoquées par des règles de validation
    *   provenants de l'utilisateur
    *
    *   string $error :: erreur
    *
    *   return void
    **/
    protected function _userError($error)
    {
        $this->error_messages[time()] = $error;
    } 
    
    public abstract function toHTML();
}


abstract class FormInput extends FormField {

    protected $min_lenght = 0;

    public function __construct($name, $label='')
    {
        parent::__construct($name, $label);
        $this->attrs['type'] = 'text';

        $this->setErrorText('minlength', 'Le texte est trop court (au moins %d caractères).');
        $this->setErrorText('maxlength', 'Le texte est trop long (pas plus de %d caractères).');
    }

    /**
    *   Change la longueur minimale du contenu du champ
    *
    *   int $len :: nouvelle valeur (0 pour désactiver la limitation)
    *
    *   return obj (le champ en question)
    **/
    public function setMinLength($len)
    {
        $this->min_lenght = (is_numeric($len) AND (int) $len > 0) ? (int) $len : 0;

        return $this;
    }

    /**
    *   Change la longueur maximale du contenu du champ
    *
    *   int $len :: nouvelle valeur (0 pour désactiver la limitation)
    *
    *   return obj (le champ en question)
    **/
    public function setMaxLength($len)
    {
        if(is_numeric($len))
        {
            if((int) $len > 0)
                $this->attrs['maxlength'] = (int) $len;
            elseif((int) $len == 0)
                unset($this->attrs['maxlength']);
        }

        return $this;
    }

    public function isValid()
    {
        if(parent::isValid())
        {
            if(strlen($this->getValue()) < $this->min_lenght)
            {
                $this->_error('minlength', $this->min_lenght);
                return False;
            }

            if(!empty($this->attrs['maxlenght']) AND strlen($this->getValue()) > $this->attrs['maxlenght'])
            {
                $this->_error('maxlength', $this->attrs['maxlenght']);
                return False;
            }

            return True;
        }

        return False;
    }
    
    public function toHTML()
    {
        $this->_makeClass();

        $attrs = $this->attrs;
        $attrs['value'] = htmlspecialchars(($this->canBound()) ? $attrs['value'] : '');

        $html = sprintf('<input %s/>', $attrs);

        return $html;
    }

    public function __toString()
    {
        return $this->toHTML();
    }
}


//Champ de texte simple
class Field_Text extends FormInput {

    public function __construct($name, $value='', $label='')
    {
        parent::__construct($name, $label);
        $this->setValue($value);
    }
    
    public function isValid()
    {
        if(parent::isValid())
            return $this->passUserValidationRules();

        return False;
    }
}


//Champ de soumission du formulaire
class Field_SubmitButton extends FormInput {

    public function __construct($name)
    {
        parent::__construct($name);
        $this->setValue($name);
        $this->required(False);

        $this->attrs['type'] = 'submit';
    }
}


//Champ de texte pour URL
class Field_URL extends FormInput {

    protected $verify_exists = False;

    public function __construct($name, $value='', $label='')
    {
        parent::__construct($name, $label);
        $this->setValue($value);

        $this->setErrorText('invalid_url', 'L\'URL n\'est pas valide.');
    }

    /**
    *   Si tourné à True, vérifiera la présence d'une page à l'url donnée
    *   (on attendra une réponse 200 OK via le protocole HTTP)
    *
    *   bool $bool :: vérification ou non
    *
    *   return obj (le champ en question)
    **/
    public function verifyExists($bool)
    {
        $this->verify_exists = (bool) $bool;

        return $this;
    }

    public function isValid()
    {
        if(parent::isValid())
        {
            if(parse_url($this->getValue(), PHP_URL_SCHEME) === False)
            {
                $this->_error('invalid_url');
                return False;
            }

            if($this->verify_exists)
            {
                $headers = @get_headers($this->getValue());
                if(!$headers)
                {
                    $this->_error('invalid_url');
                    return False;
                }
                elseif(!in_array('HTTP/1.1 200 OK', $headers) AND !in_array('HTTP/1.0 200 OK', $headers))
                {
                    $this->_error('invalid_url');
                    return False;
                }
            }

            return $this->passUserValidationRules();
        }

        return False;
    }
}

//Champ décimal :: on attend un nombre (entier)
// TODO :: gérer les nombres flottants
class Field_Decimal extends FormInput {

    protected $min = Null;
    protected $max = Null;

    public function __construct($name, $value='', $label='')
    {
        parent::__construct($name, $label);
        $this->setValue($value);

        $this->setErrorText('decimal_required', 'Doit être un nombre.');
        $this->setErrorText('lower_than_min_decimal', 'Ce nombre est trop petit (au moins %d).');
        $this->setErrorText('higher_than_max_decimal', 'Ce nombre est trop grand (au maximum %d).');
    }

    /**
    *   Définit un minimum pour le champ
    *
    *   int $int :: valeur du minimum
    *
    *   return obj (le champ en question)
    **/
    public function min($int)
    {
        $this->min = (is_numeric($int)) ? (int) $int : NULL;

        return $this;
    }

    /**
    *   Définit un maximum pour le champ
    *
    *   int $int :: valeur du maximum
    *
    *   return obj (le champ en question)
    **/
    public function max($int)
    {
        $this->max = (is_numeric($int)) ? (int) $int : NULL;

        return $this;
    }

    public function isValid()
    {
        if(parent::isValid())
        {
            if(!is_numeric($this->getValue()))
            {
                $this->_error('decimal_required');
                return False;
            }

            if($this->min !== NULL AND $this->getValue() < $this->min)
            {
                $this->_error('lower_than_min_decimal', $this->min);
                return False;
            }

            if($this->max !== NULL AND $this->getValue() > $this->max)
            {
                $this->_error('higher_than_max_decimal', $this->max);
                return False;
            }

            return $this->passUserValidationRules();
        }
        return False;
    }
}

//Textarea
class Field_Textarea extends FormInput {

    public function __construct($name, $value='', $label='')
    {
        parent::__construct($name, $label);
        $this->setValue($value);
    }

    /**
    *   Change la valeur de l'attribut cols
    *
    *   int|string $nb_cols :: nouvelle valeur (int, ou string représentant un pourcentage)
    *
    *   return obj (le champ en question)
    **/
    public function cols($nb_cols)
    {
        $percent = (substr((string) $nb_cols, -1) == '%');
        $nb_cols = rtrim($nb_cols, '%');

        if(is_numeric($nb_cols) AND $nb_cols > 0)
            $this->attrs['cols'] = ($percent) ? $nb_cols.'%' : (int) $nb_cols;

        return $this;
    }

    /**
    *   Change la valeur de l'attribut rows
    *
    *   int|string $nb_rows :: nouvelle valeur (int, ou string représentant un pourcentage)
    *
    *   return obj (le champ en question)
    **/
    public function rows($nb_rows)
    {
        $percent = (substr((string) $nb_rows, -1) == '%');
        $nb_rows = rtrim($nb_rows, '%');

        if(is_numeric($nb_rows) AND $nb_rows > 0)
            $this->attrs['rows'] = ($percent) ? $nb_rows.'%' : (int) $nb_rows;

        return $this;
    }
    
    public function isValid()
    {
        if(parent::isValid())
            return $this->passUserValidationRules();

        return False;
    }
    
    public function toHTML()
    {
        $this->_makeClass();

        $attrs = $this->attrs;
        $value = ($this->canBound()) ? htmlspecialchars($attrs['value']) : '';
        unset($attrs['value']);

        $html = sprintf('<textarea %s>%s</textarea>', $attrs, $value);

        return $html;
    }

    public function __toString()
    {
        return $this->toHTML();
    }
}


//Champ d'email
class Field_Email extends FormInput {

    public function __construct($name, $value='', $label='')
    {
        parent::__construct($name, $label);
        $this->setValue($value);

        $this->setErrorText('invalid_email', 'L\'adresse email n\'est pas valide');
    }

    public function isValid()
    {
        if(parent::isValid())
        {
            if(filter_var($this->getValue(), FILTER_VALIDATE_EMAIL))
                return $this->passUserValidationRules();

            $this->_error('invalid_email');
        }
        return False;
    }
}

//Champ caché
class Field_Hidden extends FormInput {

    public function __construct($name, $value)
    {
        parent::__construct($name);
        $this->attrs['type'] = 'hidden';
        $this->setValue($value);
    }
    
    public function isValid()
    {
        return $this->passUserValidationRules();
    }
}


//Champ de mot de passe
class Field_Password extends FormInput {

    public function __construct($name, $value='', $label='')
    {
        parent::__construct($name, $label);
        $this->attrs['type'] = 'password';
        $this->setValue($value);
        $this->autoBound(False);
    }
    
    public function isValid()
    {
        if(parent::isValid())
            return $this->passUserValidationRules();

        return False;
    }
}


//Champ de texte pour date
class Field_Date extends FormInput {

    protected $format;

    public function __construct($name, $value='', $label='')
    {
        parent::__construct($name, $label);
        $this->setValue($value);


        $this->setErrorText('invalid_format', 'Le format de référence est incorrect (%s).');
        $this->setErrorText('invalid_date', 'La date est invalide. Format à respecter : %s');
    }

    /**
    *   Change le format de date utilisé
    *
    *   string $format :: voir http://fr.php.net/manual/fr/function.date.php pour les formats
    *
    *   ATTENTION :: sous windows (strptime n'étant pas implémentée)
    *                seuls ces formats seront parsés %S, %M, %H, %d, %m, %Y
    *
    *   return obj (le champ en question)
    **/
    public function format($format)
    {
        $this->format = $format;

        return $this;
    }

    public function isValid()
    {
        if(parent::isValid())
        {
            if(empty($this->format))
            {
                $this->_error('invalid_format', $this->format);
                return False;
            }

            if(strptime($this->getValue(), $this->format) !== False)
            {
                $this->_error('invalid_date', $this->format);
                return False;
            }
            
            return $this->passUserValidationRules();
        }

        return False;
    }
}


//Champ checkbox
class Field_Bool extends FormField {

    public function __construct($name, $value=False, $label='')
    {
        parent::__construct($name, $label);
        $this->attrs['type'] = 'checkbox';
        $this->attrs['value'] = '1';

        if((bool) $value)
            $this->_checked(True);
    }

    /**
    *   Coche ou décoche la checkbox
    *
    *   bool $bool :: état de la checkbox
    *
    *   return obj (le champ en question)
    **/
    private function _checked($bool)
    {
        if((bool) $bool)
            $this->attrs['checked'] = 'checked';
        else
            unset($this->attrs['checked']);
    }

    /**
    *   Adapte setValue pour une checkbox, si la valeur en paramètre équivaut à True, on coche la checkbox
    *
    *   bool $value :: état de la checkbox
    *
    *   return obj (le champ en question)
    **/
    public function setValue($value)
    {
        $this->_checked((bool) $value);

        return $this;
    }

    /**
    *   Adapte getValue pour une checkbox, si la valeur en paramètre équivaut à True, on coche la checkbox
    *
    *   bool $value :: état de la checkbox
    *
    *   return bool
    **/
    public function getValue()
    {
        return (isset($this->attrs['checked'])) ? 1 : 0;
    }
    
    public function isValid()
    {
        if(parent::isValid())
            return $this->passUserValidationRules();

        return False;
    }
    
    public function toHTML()
    {
        $this->_makeClass();

        $attrs = $this->attrs;

        if(!$this->canBound())
            unset($attrs['checked']);

        $html = sprintf('<input %s/>', $attrs);

        return $html;
    }

    public function __toString()
    {
        return $this->toHTML();
    }
}


//Champ select
class Field_Select extends FormField {

    protected $options;

    public function __construct($name, $value='', $label='')
    {
        parent::__construct($name, $label);
    }

    /**
    *   Définit les d'options du select
    *
    *   array $array :: array contenant les options
    *                   array(
    *                           'Europe' => array(
    *                                               'fr' => 'France',
    *                                               'es' => 'Espagne'
    *                                           ),
    *                           'clef' => 'Texte à afficher',
    *                       )
    *
    *   return obj (le champ en question)
    **/
    public function options($array)
    {
        $this->options = $array;

        return $this;
    }

    /**
    *   Lance la construction des options du select
    *
    *   return string (code html des options)
    **/
    protected function _makeOptions()
    {
        $output = '<option value="">----------</option>';
        foreach($this->options as $name => $text)
        {
            $this->_proceedOptions($output, $name, $text);
        }

        return $output;
    }

    /**
    *   Fonction s'occupant de générer du HTML pour les options
    *
    *   string &$output :: référence à la variable qui contiendra le html
    *   string $name :: valeur du <option>
    *   string|array $text :: texte à afficher pour l'option
    *
    *   return void
    **/
    protected function _proceedOptions(&$output, $name, $text='')
    {
        if(is_array($text))
        {
            $output .= '<optgroup label="'.$name.'">';
            foreach($text as $key => $value)
            {
                $this->_proceedOptions($output, $key, $value);
            }
            $output .= '</optgroup>';
        }
        elseif(!empty($text))
        {
            $selected = ($name == $this->getValue() AND $this->canBound()) ? ' selected="selected"' : '';
            $output .= '<option value="'.$name.'"'.$selected.'>'.$text.'</option>';
        }
    }
    
    public function isValid()
    {
        if(parent::isValid())
            return $this->passUserValidationRules();

        return False;
    }

    public function toHTML()
    {
        $this->_makeClass();
        $options = $this->_makeOptions();

        $attrs = $this->attrs;
        unset($attrs['value']);

        $html = sprintf('<select %s>%s</select>', $attrs, $options);

        return $html;
    }

    public function __toString()
    {
        return $this->toHTML();
    }
}


//Champ d'upload
class Field_File extends FormInput {

    protected $form = Null;
    protected $valid_ext = array();
    protected $max_size = False;

    public function __construct($name, $label='', &$oForm=Null)
    {
        parent::__construct($name, $label);
        $this->attrs['type'] = 'file';
        $this->autoBound(False);

        if(is_null($oForm))
            trigger_error('Une instance du formulaire doit être passée comme troisième paramètre au champ « '.$name.' ».', E_USER_WARNING);

        //on adapte le form
        $this->form = $oForm;
        $this->form->setEnctype('multipart/form-data');

        //définition des erreurs
        $this->setErrorText('invalid_ext', 'L\'extension du fichier n\'est pas valide. Extensions autorisées : %s');
        $this->setErrorText('too_big', 'Le fichier est trop volumineux (%d octets max)');
    }

    /**
    *   Fonction permettant d'autoriser des extensions
    *
    *   array $ext :: array contenant les extensions à autoriser
    *
    *   return obj (le champ en question)
    **/
    public function addValidExtension(array $ext)
    {
        $this->valid_ext = array_merge($this->valid_ext, $ext);

        return $this;
    }

    /**
    *   Fonction permettant de définir la taille maximale du fichier
    *
    *   int $size :: taille max
    *
    *   return obj (le champ en question)
    **/
    public function setMaxSize($size)
    {
        if(!is_numeric($size) OR $size < 0)
            trigger_error('La taille maximale pour la champ « '.$this->getName().' » doit être un nombre strictement supérieur à zéro.', E_USER_WARNING);

        $this->form->add('Hidden', 'POST_MAX_SIZE')->value((int) $size);
        $this->max_size = (int) $size;

        return $this;
    }

    public function getValue()
    {
        return isset($_FILES[$this->getName()]) ? $_FILES[$this->getName()] : Null;
    }

    public function isValid()
    {
        $file = $this->getValue();

        if($this->required AND (empty($file) OR UPLOAD_ERR_NO_FILE == $file['error']))
        {
            $this->_error('required');
            return False;
        }

        if(!empty($this->valid_ext) AND $this->required AND !in_array(strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)), $this->valid_ext))
        {
            $this->_error('invalid_ext', implode(', ', $this->valid_ext));
            return False;
        }

        if($this->max_size !== False AND $file['size'] > $this->max_size)
        {
            $this->_error('too_big', $this->max_size);
            return False;
        }
        
        return ($file['error'] == UPLOAD_ERR_NO_FILE OR ($file['error'] == UPLOAD_ERR_OK && 
                is_uploaded_file($file['tmp_name']) && $this->passUserValidationRules()));
    }
}


/**
*
*   Class "accessoires" (ListArray et AttributeList trouvées je ne sais où sur l'immensité de la toile ...)
*
**/
class ListArray implements Iterator, ArrayAccess {

    protected $array = array();
    private $valid = false;

    function __construct(Array $array = array()) {
        $this->array = $array;
    }

    /* Iterator */
    function rewind()  { $this->valid = (FALSE !== reset($this->array)); }
    function current() { return current($this->array);      }
    function key()     { return key($this->array);  }
    function next()    { $this->valid = (FALSE !== next($this->array));  }
    function valid()   { return $this->valid;  }

    /* ArrayAccess */
    public function offsetExists($offset) {
        return isset($this->array[$offset]);
    }
    public function offsetGet($offset) {
        return $this->array[$offset];
    }
    public function offsetSet($offset, $value) {
        return $this->array[$offset] = $value;
    }
    public function offsetUnset($offset) {
        unset($this->array[$offset]);
    }
}


class AttributeList extends ListArray {

    public function __toString() {
        $output = '';
        if (!empty($this->array)) {
            foreach($this->array as $a => $v) {
                $output .= sprintf('%s="%s" ', $a, $v);
            }
        }
        return $output;
    }
}

//trouvé ici :: http://fr.php.net/manual/fr/function.strptime.php#86572
/**
 * Parse a time/date generated with strftime().
 *
 * This function is the same as the original one defined by PHP (Linux/Unix only),
 *  but now you can use it on Windows too.
 *  Limitation : Only this format can be parsed %S, %M, %H, %d, %m, %Y
 *
 * @author Lionel SAURON
 * @version 1.0
 * @public
 *
 * @param $sDate(string)    The string to parse (e.g. returned from strftime()).
 * @param $sFormat(string)  The format used in date  (e.g. the same as used in strftime()).
 * @return (array)          Returns an array with the <code>$sDate</code> parsed, or <code>false</code> on error.
 */
if(function_exists("strptime") == false)
{
    function strptime($sDate, $sFormat)
    {
        $aResult = array
        (
            'tm_sec'   => 0,
            'tm_min'   => 0,
            'tm_hour'  => 0,
            'tm_mday'  => 1,
            'tm_mon'   => 0,
            'tm_year'  => 0,
            'tm_wday'  => 0,
            'tm_yday'  => 0,
            'unparsed' => $sDate,
        );

        while($sFormat != "")
        {
            // ===== Search a %x element, Check the static string before the %x =====
            $nIdxFound = strpos($sFormat, '%');
            if($nIdxFound === false)
            {

                // There is no more format. Check the last static string.
                $aResult['unparsed'] = ($sFormat == $sDate) ? "" : $sDate;
                break;
            }

            $sFormatBefore = substr($sFormat, 0, $nIdxFound);
            $sDateBefore   = substr($sDate,   0, $nIdxFound);

            if($sFormatBefore != $sDateBefore) break;

            // ===== Read the value of the %x found =====
            $sFormat = substr($sFormat, $nIdxFound);
            $sDate   = substr($sDate,   $nIdxFound);

            $aResult['unparsed'] = $sDate;

            $sFormatCurrent = substr($sFormat, 0, 2);
            $sFormatAfter   = substr($sFormat, 2);

            $nValue = -1;
            $sDateAfter = "";
            switch($sFormatCurrent)
            {
                case '%S': // Seconds after the minute (0-59)

                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);

                    if(($nValue < 0) || ($nValue > 59)) return false;

                    $aResult['tm_sec']  = $nValue;
                    break;

                // ----------
                case '%M': // Minutes after the hour (0-59)
                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);

                    if(($nValue < 0) || ($nValue > 59)) return false;

                    $aResult['tm_min']  = $nValue;
                    break;

                // ----------
                case '%H': // Hour since midnight (0-23)
                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);

                    if(($nValue < 0) || ($nValue > 23)) return false;

                    $aResult['tm_hour']  = $nValue;
                    break;

                // ----------
                case '%d': // Day of the month (1-31)
                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);

                    if(($nValue < 1) || ($nValue > 31)) return false;

                    $aResult['tm_mday']  = $nValue;
                    break;

                // ----------
                case '%m': // Months since January (0-11)
                    sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);

                    if(($nValue < 1) || ($nValue > 12)) return false;

                    $aResult['tm_mon']  = ($nValue - 1);
                    break;

                // ----------
                case '%Y': // Years since 1900
                    sscanf($sDate, "%4d%[^\\n]", $nValue, $sDateAfter);

                    if($nValue < 1900) return false;

                    $aResult['tm_year']  = ($nValue - 1900);
                    break;

                // ----------
                default: break 2; // Break Switch and while
            }

            // ===== Next please =====
            $sFormat = $sFormatAfter;
            $sDate   = $sDateAfter;

            $aResult['unparsed'] = $sDate;

        } // END while($sFormat != "")


        // ===== Create the other value of the result array =====
        $nParsedDateTimestamp = mktime($aResult['tm_hour'], $aResult['tm_min'], $aResult['tm_sec'],
                                $aResult['tm_mon'] + 1, $aResult['tm_mday'], $aResult['tm_year'] + 1900);

        // Before PHP 5.1 return -1 when error
        if(($nParsedDateTimestamp === false)
        ||($nParsedDateTimestamp === -1)) return false;

        $aResult['tm_wday'] = (int) strftime("%w", $nParsedDateTimestamp); // Days since Sunday (0-6)
        $aResult['tm_yday'] = (strftime("%j", $nParsedDateTimestamp) - 1); // Days since January 1 (0-365)

        return $aResult;
    } // END of function

} // END if(function_exists("strptime") == false)

?>
