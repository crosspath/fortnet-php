<?php

class Ini
{
  protected $t = null, $ini_file = '';
  
  public function __construct($file)
  {
    $this -> ini_file = $file;
  }
  
  public function get($key)
  {
    if ($this -> t === null)
      $this -> read();
    return $this -> t[$key];
  }
  
  protected function read()
  {
    $this -> t = parse_ini_file($this -> ini_file);
    if ($this -> t === false)
      throw new E\File($this -> ini_file);
  }
}
