<?php

class ArrayFx
{
  public static function first($a)
  {
    $b = $a;
    return array_shift($b);
  }
  
  public static function last($a)
  {
    $b = $a;
    return array_pop($b);
  }
  
  public static function array_insert($array, $value, $pos)
  {
    return array_merge(array_slice($array, 0, $pos), (array)$value, array_slice($array, $pos));
  }
  
}
