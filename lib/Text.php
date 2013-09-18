<?php

class Text
{
  protected static $t = null;
  const INI_FILE = 'config/texts.ini';
  
  public static function get($key, $params = array())
  {
    $t = self :: $t[$key];
    if ($params && count($params))
      $t = strtr($t, self :: prepare_keys($params));
    return $t;
  }
  
  public static function read()
  {
    self :: $t = parse_ini_file('config/texts.ini');
    if (self :: $t === false)
      throw new E\File(self :: INI_FILE);
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
