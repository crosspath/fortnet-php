<?php

class Text
{
  protected static $ini = null;
  const INI_FILE = 'config/texts.ini';
  
  public static function get($key, $params = array())
  {
    if (self :: $ini === null)
      self :: $ini = new Ini(self :: INI_FILE);
    
    $t = self :: $ini -> get($key);
    if ($params && count($params))
      $t = strtr($t, self :: prepare_keys($params));
    return $t;
  }
  
  protected static function prepare_keys($params)
  {
    $ret = array();
    foreach ($params as $k => $v)
      $ret[":$k"] = $v;
    return $ret;
  }
}

// short function
function t($key, $params = array())
{
  return Text :: get($key, $params);
}
