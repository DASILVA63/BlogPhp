<?php

// raised when no match is found between patterns and the requested url
class Error404 extends Exception {
    
    private $requested_url;
    
    public function __construct($requested_url)
    {
        $this->requested_url = $requested_url;
        $this->message = $requested_url.' not found';
    }
    
    public function getRequestedURL()
    {
        return $this->requested_url;
    }
}


class UrlDispatcher {
    
    private $rules = array();
    private $auto_trailing_slash = True;
    private $base_dir = '';
    
    
    public function __construct(array $patterns=array(), $base_dir='')
    {
        if(!empty($base_dir))
            $this->base_dir = $base_dir;
        
        if(!empty($patterns))
            $this->addPatterns($patterns);
    }
    
    /**
    *   Add mapping rules
    * 
    *   array $array_patterns :: rules to add
    *   -> FORMAT :: To do !
    *   -> EXEMPLE ::
    *       $array_patterns = array(
    *           array('index/' => 'yop.txt'),
    *           array('index-(?P<id>\d+)/' => 'yop.txt'),
    *           array('news-(?P<id>\d+)/' => 'func'),
    *           array('news/' => 'func'),
    *           array('news-extra/', 'func', 'extra-params' => array('id'=> 33)), // the order is important ! (pattern, callback, options)
    *       );
    *   
    *   return void
    **/
    public function addPatterns(array $array_patterns)
    {
        foreach($array_patterns as $foo => $rule)
            if(count($rule) > 1)
            {
                $pattern = array_shift($rule);
                
                $this->rules[$pattern] = $rule;
            }
            else
                $this->rules = array_merge($this->rules, $rule);
    }
    
    /**
    *   We handle the requested URl with the previously given patterns.
    * 
    *   string $requested_url :: requested URL
    *   bool $args_in_GET :: if True, matched params will be merged with
    *                        $_GET. Else they'll be send to a callback
    *   
    *   return bool :: match found ?
    **/
    public function handle($requested_url='', $args_in_GET=False)
    {
        if(empty($requested_url))
            $requested_url = $_SERVER['REQUEST_URI'];
        
        // to delete $this->base_dir in $requested_url
        if(!empty($this->base_dir))
            $requested_url = str_replace($this->base_dir, '', $requested_url);
		
		$data = explode('?', $requested_url);
        
        if(isset($data[1]))
            parse_str($data[1], $_GET);
		
		$requested_url = $data[0];
        
        // we add the trailing slash to the URL (if needed and asked)
        if($this->isAutoTrailingSlash())
            $requested_url = $this->_addTrailingSlash($requested_url);
        
        foreach($this->rules as $pattern => $data)
        {
            $matches = array();
            
            // if the URL doesn't match the pattern -> next !
            if(!preg_match('`'.$pattern.'`', $requested_url, $matches))
                continue;
            
            $callable_name = '';
            $data = $this->_parseCallbackData($data);
			
			// we "clean" the parameters
            $args = $this->_filterCallbackParams($matches);
                
            // we add the "extra-params" to the matched parameters
            $args = array_merge($args, $data['extra-params']);
			$_GET = array_merge($_GET, $args);
			
            // a callback is associated with the pattern
            if(is_callable($data['target'], False, $callable_name))
            {    
                // we call the ... callback !
                $this->_execCallback($callable_name, $args, $args_in_GET);
            }
            // a file is associated with the pattern
            // add tests ! --> is_file() ?
            else
			{
				require_once($data['target']);
			}
            
            return;
        }
        
        throw new Error404($requested_url);
    }
    
    /**
    *   Allow to enable or disable the automatic trailing slash adding
    * 
    *   bool $bool :: activation state
    * 
    *   return void
    **/
    public function setAutoTrailingSlash($bool)
    {
        $this->auto_trailing_slash = (bool) $bool;
    }
    
    /**
    *   Return the activation state of the automatic trailing slash adding
    * 
    *   return bool
    **/
    public function isAutoTrailingSlash()
    {
        return $this->auto_trailing_slash;
    }
    
    /**
    *   Create a wordpress-like htaccess
    * 
    *   string $base_dir :: website's root dir
    *   string $receiver_file :: file wich will receive the redirected
    *                            requests (index.php is fine)
    * 
    *   return string
    **/
    public static function createHtaccess($base_dir, $receiver_file='index.php')
    {
        return <<<TXT
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /$base_dir/$receiver_file [L]
</IfModule>
TXT;
    }
    
    /**
    *   Add a trailing slash (only if needed)
    * 
    *   string $url :: an URL
    * 
    *   return string :: the same URL (+ /)
    **/
    private function _addTrailingSlash($url)
    {
        return (substr($url, -1) == '/') ? $url : $url.'/';
    }
    
    /**
    *   Fill an array containing the callback to execute and the
    *   additionnals params given to the function.
    * 
    *   mixed $data :: data to parse
    * 
    *   return array :: array filled with the data
    **/
    private function _parseCallbackData($data)
    {
        $ret = array();
        
        $ret['target'] = (is_array($data)) ? array_shift($data) : $data;
        $ret['extra-params'] = (is_array($data) AND isset($data['extra-params'])) ? $data['extra-params'] : array();
        
        return $ret;
    }
    
    /**
    *   Clean an array from it's numeric keys
    * 
    *   array $params :: array to clean
    * 
    *   return array :: array cleaned
    **/
    private function _filterCallbackParams(array $params)
    {
        foreach($params as $key => $value)
            if(is_int($key))
                unset($params[$key]);
        
        return $params;
    }
    
    /**
    *   Call the callback with the rights params
    * 
    *   callback $callback :: the callback (is_valid($callback) == True !)
    *   array $params :: params
    *   bool $args_in_GET :: if True, matched params will be merged with
    *                        $_GET. Else they'll be send to a callback
    *   
    *   return void
    **/
    private function _execCallback($callback, array $params, $args_in_GET=False)
    {
        if(!$args_in_GET)
            // we send the params to the callback
            call_user_func_array($callback, $params);
        else
        {
            // or we put the params into $_GET
            $_GET = array_merge($_GET, $params);
            
            call_user_func($callback);
        }
    }
}
/*
$urls = array(

            array('^index/$' => 'yop.txt'),
            array('^index-(?P<id>\d+)/$' => 'yop.txt'),
            array('^news-(?P<id>\d+)/$' => 'func'),
            array('^news-(?P<id>\d+)/foo/$' => 'test'),
            array('^news/$' => 'func'),
            array('^/(?P<module>\w+)/$' => 'test'),
            array('^/(?P<module>\w+)/(?P<action>\w+)/$' => 'test'),
            array('^/(?P<module>\w+)/(?P<action>\w+)/(?P<id>\d+)/$' => 'test'),
            array('^news-extra/$', 'func', 'extra-params' => array('id'=> 33)),
        );

function func($id=1)
{
    var_dump($_GET);
    echo 'bouh'.$id.'<br />';
}

function test($module, $action='', $id=-1)
{
    echo 'module : «'.$module.'»<br />';
    echo 'action : «'.$action.'»<br />';
    echo 'id : «'.$id.'»<br />';
}

$time = microtime(True);

$dispatcher = new UrlDispatcher($urls, '/url_dispatcher/');
try {
    $dispatcher->handle();
} catch (Error404 $e) {
    var_dump($e->getMessage());
}

echo microtime(True) - $time;
*/