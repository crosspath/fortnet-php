<?php

class EntersHelper
{
  public function export($f)
  {
    $person = $f['person'] ? "---{$f['person']}" : '';
    return "{$f['date_start']}---{$f['date_end']}{$person}.xlsx";
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
  
  protected static $sensors_struct = array(
    'in' => array(
      'lift' => array('4' => 227, '6' => 209),
      'stairs' => array('4' => 213, '6' => 228)
    ),
    'out' => array(
      'lift' => array('4' => 210, '6' => 226),
      'stairs' => array('4' => 229, '6' => 212)
    )
  );
  protected static $sensors = array(
    227 => array('in', 'lift', '4'),
    209 => array('in', 'lift', '6'),
    213 => array('in', 'stairs', '4'),
    228 => array('in', 'stairs', '6'),
    210 => array('out', 'lift', '4'),
    226 => array('out', 'lift', '6'),
    229 => array('out', 'stairs', '4'),
    212 => array('out', 'stairs', '6')
  );
  
  public function extract_status($v)
  {
    $s = self :: $sensors[$v];
    $a = array();
    switch ($s[0])
    {
      case 'in': $a[] = '&rarr; вошёл'; break;
      case 'out': $a[] = '&nbsp;&nbsp;&nbsp;&nbsp; вышел &larr;'; break;
    }
    $a[] = "{$s[2]} этаж";
    switch ($s[1])
    {
      case 'lift': $a[] = 'лифт'; break;
      case 'stairs': $a[] = 'лестница'; break;
    }
    return $a;
  }
}
/*
Через лифт:
209 - вошёл, 6 этаж
210 - вышел, 4 этаж
226 - вышел, 6 этаж
227 - вошёл, 4 этаж

Через лестницу:
212 - вышел, 6 этаж
213 - вошёл, 4 этаж
228 - вошёл, 6 этаж
229 - вышел, 4 этаж

switch ($s[0])
{
  case 'in': $v .= ' вошёл'; break;
  case 'out': $v .= ' вышел'; break;
}
$v .= " на {$s[2]} этаже";
switch ($s[1])
{
  case 'lift': $v .= $s[0] == 'in' ? ' из лифта' : ' к лифту'; break;
  case 'stairs': $v .= $s[0] == 'in' ? ' с лестницы' : ' к лестнице'; break;
}
*/