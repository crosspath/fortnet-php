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
    $res = array();
    foreach ($visits as $person => $rows)
    {
      $record = array(null, $person);
      
      foreach ($rows as $row)
      {
        $record[0] = $row['THIS_DAY'];
        $record[2] = $row['MIN_DATETIME'];
        $record[3] = $row['MAX_DATETIME'];
        $record[4] = $row['DIFF'][0];
        $record[5] = $row['DIFF'][1];
        $record[6] = $row['DIFF'][2];
      }
      
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
}
