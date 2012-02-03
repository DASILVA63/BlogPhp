<?php
if(!defined('IN') OR IN !== True)
    die('fatal error :: you can\'t access this page directly !');

abstract class ObjectBase {
    
    protected $dn = array();
    
    
    public function __construct(array &$data=array())
    {
        $this->dn = $data;
    }
    
    public function __get($key)
    {
        return (isset($this->dn[$key])) ? $this->dn[$key]: NULL;
    }
    
    public function getKeys()
    {
        return array_keys($this->dn);
    }
    
    public function asArray()
    {
        return $this->dn;
    }
    
    public function updateData(array &$data)
    {
        foreach($data as $key => $val)
            $this->$key = $val;
    }
}
?>
