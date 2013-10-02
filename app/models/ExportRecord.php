<?php

class ExportRecord
{
  public static $columns;
  
  public static function columns()
  {
    if (empty(self :: $columns))
      self :: $columns = array(
        t('app.export.col_date'),
        t('app.export.col_name'),
        t('app.export.col_in'),
        t('app.export.col_out'),
        t('app.export.col_diff'),
        t('app.export.col_lunch'),
        t('app.export.col_res')
      );
    return self :: $columns;
  }
  
  public static $top_row = 1;
  
  public static function table($visits)
  {
    $day = function($full_date) {$date = explode('-', $full_date); return $date[2];}; // только день месяца
    $time = function($full_date) {$time = explode(' ', $full_date); return $time[1];}; // только время
    $res = array();
    foreach ($visits as $person => $rows)
    {
      $record = array(null, $person);
      $sum = null;
      
      foreach ($rows as $row)
      {
        $record[0] = $day($row['THIS_DAY']);
        $record[2] = isset($row['MIN_DATETIME']) ? $time($row['MIN_DATETIME']) : '';
        $record[3] = isset($row['MAX_DATETIME']) ? $time($row['MAX_DATETIME']) : '';
        if (isset($row['DIFF']))
        {
          if (is_a($row['DIFF'][0], 'DateInterval'))
            $record[4] = $row['DIFF'][0] -> format('%h:%I:%s');
          $record[5] = $row['DIFF'][1] . ':00';
          if (is_a($row['DIFF'][2], 'DateInterval'))
          {
            $record[6] = $row['DIFF'][2] -> format('%h:%I:%s');
            $sum = $sum ? Record :: di_add($sum, $row['DIFF'][2]) : $row['DIFF'][2];
          }
        }
        $res[] = $record;
      }
      // sum
      $record[0] = '';
      $record[2] = '';
      $record[3] = '';
      $record[4] = '';
      $record[5] = t('app.export.sum');
      $record[6] = $sum ? $sum -> format('%h:%I:%s') : 'н/д';
      $res[] = $record;
    }
    return $res;
  }
  
  public static function coord_column($col)
  {
    $interval = range('A', 'Z');
    $column_times = floor((float)$col / count($interval));
    $column_times_letter = $column_times ? $interval[$column_times-1] : '';
    return $column_times_letter . $interval[$col % count($interval)];
  }
  /*
  public static function add_empty_rows($visits, $date_start, $date_finish)
  {
    $res_res = array();
    foreach ($visits as $person => $rows)
    {
      $res = array();
      $prev_date = new DateTime($date_start);
      var_dump($date_start);
      var_dump($prev_date);
      echo '<br>**<br>';
      foreach ($rows as $key => $row)
      {
        var_dump(self :: next_date($prev_date));
        var_dump(new DateTime($row['THIS_DAY']));
        echo '<br>';
        for ($i = self :: next_date($prev_date); $i < new DateTime($row['THIS_DAY']); $i = self :: next_date($i))
          $res[] = array('THIS_DAY' => $i -> format('Y-m-d'));
        $prev_date = $row['THIS_DAY'];
        $res[] = $row;
      }
      for ($i = self :: next_date($prev_date); $i <= new DateTime($date_finish); $i = self :: next_date($i))
        $res[] = array('THIS_DAY' => $i -> format('Y-m-d'));
      $res_res[$person] = $res;
    }
    return $res_res;
  }
  */
  public static function add_empty_rows($visits, $date_start, $date_finish)
  {
    $res_res = array();
    foreach ($visits as $person => $rows)
    {
      $date_range = self :: date_range($date_start, $date_finish, 'Y-m-d');
      $res = array();
      foreach ($rows as $key => $row)
      {
        while ($row['THIS_DAY'] != $date_range[0])
          $res[] = array('THIS_DAY' => array_shift($date_range));
        $res[] = $row;
        array_shift($date_range);
      }
      foreach ($date_range as $date)
          $res[] = array('THIS_DAY' => $date);
      $res_res[$person] = $res;
    }
    return $res_res;
  }
  
  public static function array_insert($array, $value, $pos)
  {
    return array_merge(array_slice($array, 0, $pos), (array)$value, array_slice($array, $pos));
  }
  
  protected static function next_date($date)
  {
    if (is_string($date))
      $date = new DateTime($date);
    return $date -> add(DateInterval :: createFromDateString('1 days'));
  }
  
  protected static function date_range($from, $to, $format = null)
  {
    $res = array();
    if (is_string($from))
      $from = new DateTime($from);
    if (is_string($to))
      $to = new DateTime($to);
    for ($i = $from; $i <= $to; $i = self :: next_date($i))
      $res[] = $format ? $i -> format($format) : $i;
    return $res;
  }
}
