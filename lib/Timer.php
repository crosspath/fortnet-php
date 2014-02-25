<?php

class Timer implements ArrayAccess, Iterator
{
  protected static $instance = null;
  protected $ticks = array();
  protected $params = null, $file = null;
  protected $position = 0;
  
  private function __construct()
  {
    $this -> params = require 'config/timer.php';
    if ($this -> params['enabled'] && $this -> params['write_to_file'])
    {
      $this -> file = fopen('log/'.strftime('%Y-%m-%d %H-%M-%S').'.txt', 'w');
      flock($this -> file, LOCK_EX);
    }
  }
  
  public function __destruct()
  {
    if ($this -> file)
    {
      flock($this -> file, LOCK_UN);
      fclose($this -> file);
    }
  }
  
  public static function get()
  {
    if (!self :: $instance)
      self :: $instance = new self();
    return self :: $instance;
  }
  
  public function add($message = '')
  {
    if ($this -> params['enabled'])
    {
      $t = new TimerTick($message);
      if ($this -> params['write_to_file'])
      {
        $prev = ($this -> ticks) ? ArrayFx::last($this -> ticks) -> timestamp : 0;
        fputs($this -> file, $t -> toString($prev) . PHP_EOL);
      }
      $this -> ticks[] = $t;
    }
  }
  
  public function clear()
  {
    if ($this -> params['enabled'])
    {
      $this -> ticks = array();
      if ($this -> params['write_to_file'])
        ftruncate($this -> file, 0);
    }
  }
  
  public function __toString()
  {
    $ret = '';
    foreach ($this -> ticks as $tick)
      $ret .= $tick . PHP_EOL;
    return $ret;
  }
  
  public function offsetExists($offset) { return array_key_exists($this -> ticks, $offset); }
  public function offsetGet($offset) { return $this -> offsetExists($offseet) ? $this -> ticks[$offset] : null; }
  public function offsetSet($offset, $value) { is_null($offset) ? $this -> ticks[] = $value : $this -> ticks[$offset] = $value; }
  public function offsetUnset($offset) { unset($this -> ticks[$offset]); }
  
  public function current() { return $this -> ticks[$this -> position]; }
  public function key() { return $this -> position; }
  public function next() { ++$this->position; }
  public function rewind() { $this->position = 0; }
  public function valid() { return $this -> offsetExists($this -> position); }
}

class TimerTick
{
  public $timestamp, $message;

  public function __construct($message = '')
  {
    $this -> timestamp = microtime(true);
    $this -> message = strval($message);
  }
  
  public function toString($previous_timestamp)
  {
    $diff = $previous_timestamp ? str_pad($this -> calc_diff($previous_timestamp), 3, ' ', STR_PAD_LEFT) . ' ms ' : '';
    return "{$diff}{$this}";
  }
  
  public function __toString()
  {
    $p = str_pad($this -> timestamp, 17);
    return "[{$p}] {$this -> message}";
  }
  
  protected function calc_diff($previous_timestamp)
  {
    return round(($this -> timestamp - $previous_timestamp) * 1000);
  }
}