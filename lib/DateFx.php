<?php

class DateFx
{

  public static function next_date($date)
  {
    if (is_string($date))
      $date = new DateTime($date);
    return $date -> add(DateInterval :: createFromDateString('1 days'));
  }
  
  public static function date_range($from, $to, $format = null)
  {
    if (is_string($from))
      $from = new DateTime($from);
    if (is_string($to))
      $to = new DateTime($to);
      
    $res = array();
    for ($i = $from; $i <= $to; $i = self :: next_date($i))
      $res[] = $format ? $i -> format($format) : $i;
    
    return $res;
  }
  
  // Только день месяца
  public static function day($full_date)
  {
    $date = explode('-', $full_date);
    return $date[2];
  }
  
  // Только время
  public static function time($full_date)
  {
    $date = explode(' ', $full_date);
    return $date[1];
  }
}
