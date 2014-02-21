<?php

/**
 * Обёртка для Interbase/Firebird
 *
 * Singleton
 */
class Db
{
  protected static $conn = null;
  protected $h = null;
  protected $prepared_queries = array(); // {hash: query_resource}
  
  public static function get()
  {
    if (!self :: $conn)
      self :: $conn = new self();
    return self :: $conn;
  }
  
  //-------
  
  protected function __construct()
  {
    $p = require 'config/database.php';
    $enc = isset($p['encoding']) ? $p['encoding'] : 'UTF8';
    $this -> h = ibase_connect($p['database'], $p['username'], $p['password'], $enc);
  }
  
  public function __destruct()
  {
    ibase_close($this -> h);
  }
  
  //-------
  
  public function query($query, $params = array())
  {
    if (!$params || !count( $params ))
      $r = ibase_query($this -> h, $query);
    else
    {
      $p = $this -> prepared_query($query);
      $r = call_user_func_array('ibase_execute', array_merge(array($p), $params));
      if ($r === false)
        throw new E\Db(t('exception.database.execute_query', array('query' => $query)));
    }
    return $r;
  }
  
  public function fetch($result_resource, $assoc = true)
  {
    if ($assoc)
      $a = ibase_fetch_assoc($result_resource);
    else
      $a = ibase_fetch_row($result_resource);
    return $a;
  }
  
  public function fetch_all($result_resource, $assoc = true)
  {
    $a = array();
    while ($b = $this -> fetch($result_resource, $assoc))
      $a[] = $b;
    return $a;
  }
  
  public function select($query, $params = array())
  {
    $q = $this -> query($query, $params);
    $f = $this -> fetch_all($q);
    return $f;
  }
  
  //-------
  
  protected function hash($query)
  {
    return hash('adler32', $query, true);
  }
  
  protected function prepared_query($query)
  {
    $hash = $this -> hash($query);
    if (!isset($this -> prepared_queries[$hash]))
    {
      try {$p = ibase_prepare($this -> h, $query);} catch (ErrorException $e)
      {
        throw new E\Db(
          t('exception.database.prepare_query', array('query' => $query)) . '<br>' .
          t('exception.database.prepare_query.syntax', array('msg' => $e -> getMessage()))
        );
      }
      if (!$p)
        throw new E\Db(t('exception.database.prepare_query', array('query' => $query)));
      $this -> prepared_queries[$hash] = $p;
    }
    return $this -> prepared_queries[$hash];
  }
}
