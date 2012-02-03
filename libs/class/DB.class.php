<?php
if(!defined('IN') OR IN !== True)
    die( 'fatal error :: you can\'t access this page directly !' );


final class DB extends PDO {
    
    private $all_query    = array();      // pour visualiser toutes les requêtes
    private $time         = array();      // temps des requêtes
                                          // types de bases de données autorisés
    private $type         = '';
    private $types        = array('mysql', 'odbc', 'sybase', 'dblib', 'mssql', 
                                  'firebird', 'ibm', 'informix', 'oci', 'pgsql',
                                  'sqlite', '4D');

    private $o_last_query = Null;
    
    /**
    *
    *   Constructeur, permet d'établir la connexion
    *
    *   string $hote  :: hôte du serveur
    *   string $login :: identifiant de connexion
    *   string $pass  :: mot de passe de la connexion
    *   string $base  :: base de données à laquelle se connecter
    *   string $type  :: type de base de données
    *
    **/

    public function __construct($hote, $login, $pass, $base, $type='mysql')
    {
        if(!in_array($type, $this->types))
            $this->trigger(__LINE__, __FILE__, 'Type de base de donnée non pris en charge');
        
        $this->type = $type;
        
        try
        {
            parent::__construct($type.':host='.$hote.';dbname='.$base, 
                                $login, $pass);
        }
        catch(Exception $e)
        {
            var_dump($e->getMessage());
            exit(1);
        }
        
        $this->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		
		//temporaire
		if($type == 'mysql')
            $this->query('SET NAMES utf8');
    }
    
    /**
    *
    *   exécuter une requête
    *
    *   string $requete :: code SQL de la requête
    *   array $args :: arguments à passer pour la requête
    *   bool $prepare :: passer par une requête préparée ou non ?
    *
    *   return bool|PDO Statement (False en cas d'erreur)
    *
    **/

    public function query($requete, array $args=array(), $prepare=True)
    {       
        $time = microtime(true);
        $count = count($this->all_query);
        $this->all_query[$count] = $requete;

        if(!$prepare)
            $retour = parent::query($requete);
        else
        {
            $retour = $this->prepare($requete);
            if(!$retour)
                var_dump($requete);
            $this->o_last_query = &$retour;
            
            if(!$retour->execute($args))
                return False;
        }
        
        $this->o_last_query = &$retour;
        
        $retour->setFetchMode(PDO::FETCH_ASSOC);

        $this->time[$count] = round(microtime(true) - $time, 8);

        return $retour;
    }
    
    /**
    *
    *   affichage d'un message d'erreur, et arrêt du script
    *
    *   int $line :: ligne de l'erreur (int)
    *   string $file :: fichier concerné (string)
    *   array|string $msg :: message d'erreur (si vide, on tente de récupérer celui de PDO)
    *
    *   return void
    *
    **/
    
    public function trigger($line, $file, $msg = '')
    {
        $error_infos = is_array($msg) ? $msg : $this->o_last_query->errorInfo();
        
        $final = '<h1>Erreur :</h1>
                <p>Une erreur de type <b>SQL</b> a &eacute;t&eacute; d&eacute;t&eacute;ct&eacute;e.<br />
                <b>Fichier :</b> '.$file.'<br />
                <b>Ligne :</b> '.$line.'<br />';

        $final .= (!empty($msg) AND is_string($msg)) ? '<b>Message d\'erreur : </b> '.$msg.'<br />' : '<b>Message d\'erreur : </b> '.$error_infos[2].'<br />';
        
        if(@$this->error)
            $final .= '<b>Num&eacute;ro d\'erreur : </b> '.$error_infos[0].'<br />';
        
        $final .= (isset($this->all_query[count($this->all_query)-1])) ? '<b>Requ&ecirc;te concern&eacute;e : </b> '.$this->all_query[count($this->all_query)-1].'</p>' : '</p>';

        exit($final);
    }
    
    /**
    *
    *   Affiche les informations de debugage
    *
    *   bool $popup :: veut-on les infos de debug sous la forme d'une popup ?
    *
    **/
    
    public function debug($popup=false) {
        
        $debug =  '<p>Page g&eacute;n&eacute;r&eacute;e avec '.count($this->all_query).' requ&ecirc;tes SQL .</p>
        <h2>Les requ&ecirc;tes sont les suivantes :</h2><p><code><ul>';
        foreach($this->all_query as $query => $value)
        {
            $debug .= '<li>'.$value.' ('.$this->time[$query].' secondes)</li><br />';
        } 
        $debug .= '</ul></code></p>';

        $debug .= '<p>Temps SQL total : '.array_sum($this->time).' secondes</p>';
        
        if($popup)
        {
            file_put_contents('debug.php', $debug);//on écrit dans le fichier

            echo '<script language="javascript" type="text/javascript">
            <!--
                window.open(\'debug.php\', \'Informations de debugage\', \'width=600, height=800,scrollbars=yes\');
            -->
            </script>';
        }
        else echo $debug;
    }
}
