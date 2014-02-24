<?php

class EntersHelper
{
  public function export($f)
  {
    $app = App :: get_app();
    $prefix = $app -> conf('export');
    
    $person = $f['person_name'] ? "---{$f['person_name']}" : '';
    return "$prefix{$f['date_start']}---{$f['date_end']}{$person}.xlsx";
  }
  
  public function extract_time($v)
  {
    $dt = explode(' ', $v);
    return substr($dt[1], 0, 5);
  }
  
  public function work_time($rows)
  {
    if (count($rows) == 1)
      return 'н/д';
    $first = array_shift($rows);
    $last = array_pop($rows);
    
    list($total, $rest, $subtracted) = Record :: work_time($first['DATETIME'], $last['DATETIME']);
    
    return "$total &ndash; $rest:00 =  $subtracted";
  }
  
}
