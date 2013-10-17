<?php

class ExportRecord
{
  const CALCULATED_MARKER = '%calculated%';
  const SUM_MARKER = '%sum%';
  const TOP_ROW = 1;
  public static $columns;
  
  public $xs = null;
  public $coord_col = array();
  
  public function __construct(PHPExcel_Worksheet $xs)
  {
    $this -> xs = $xs;
  }
  
  public function columns()
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
  
  public function put_columns()
  {
    $top_row = self :: TOP_ROW;
    foreach ($this -> columns() as $key => $c)
    {
      $col = $this -> coord_column($key);
      $this -> xs -> SetCellValue("{$col}{$top_row}", $c);
      $this -> xs -> getColumnDimension($col) -> setAutoSize(true);
      $this -> coord_col[] = $col;
    }
  }
  
  public function put_data(array $result)
  {
    $counter = self :: TOP_ROW + 1;
    $bg_color = $this -> random_color();
    $first_row_for_person = $counter;
    $col = $this -> coord_col;
    
    foreach ($result as $record) // rows
    {
      foreach ($record as $k => $value) // columns/cells
      {
        $cell = $col[$k] . $counter;
        $calculated = $record[$k] == self :: CALCULATED_MARKER;
        $sum = $record[$k] == self :: SUM_MARKER;
        
        if ($calculated)
          $cell_value = $this -> calculated_cell($k, $counter);
        elseif ($sum)
        {
          $cell_value = $this -> sum_cell($col[$k], $first_row_for_person, $counter-1);
          $this -> xs -> getStyle($this -> area($first_row_for_person, $counter)) -> applyFromArray(
            $this -> area_styles($bg_color)
          );
          $first_row_for_person = $counter + 1;
          $bg_color = $this -> random_color();
        }
        else
          $cell_value = $record[$k];
        
        $this -> xs -> SetCellValue($cell, $cell_value);
        if ($calculated || $sum || preg_match('/:.+:/', $record[$k]))
          $this -> xs -> getStyle($cell) -> getNumberFormat() -> setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME4);
      }
      $counter++;
    }
  }
  
  public function table($visits)
  {
    $res = array();
    foreach ($visits as $person => $rows)
    {
      $record = array(null, $person);
      $sum = null;
      
      foreach ($rows as $row)
      {
        $record[0] = DateFx :: day($row['THIS_DAY']);
        $record[2] = isset($row['MIN_DATETIME']) ? DateFx :: time($row['MIN_DATETIME']) : '';
        $record[3] = isset($row['MAX_DATETIME']) ? DateFx :: time($row['MAX_DATETIME']) : '';
        /*if (isset($row['DIFF']))
        {
          if (is_a($row['DIFF'][0], 'DateInterval'))
            $record[4] = $row['DIFF'][0] -> format('%h:%I:%s');
          $record[5] = $row['DIFF'][1] . ':00';
          if (is_a($row['DIFF'][2], 'DateInterval'))
          {
            $record[6] = $row['DIFF'][2] -> format('%h:%I:%s');
            $sum = $sum ? Record :: di_add($sum, $row['DIFF'][2]) : $row['DIFF'][2];
          }
        }*/
        $record[4] = $record[5] = $record[6] = self :: CALCULATED_MARKER;
        $res[] = $record;
      }
      // sum
      $record[0] = '';
      $record[2] = '';
      $record[3] = '';
      $record[4] = '';
      $record[5] = t('app.export.sum');
      $record[6] = self :: SUM_MARKER; //$sum ? $sum -> format('%h:%I:%s') : 'н/д';
      $res[] = $record;
    }
    return $res;
  }
  
  public function coord_column($col)
  {
    $interval = range('A', 'Z');
    $column_times = floor((float)$col / count($interval));
    $column_times_letter = $column_times ? $interval[$column_times-1] : '';
    return $column_times_letter . $interval[$col % count($interval)];
  }
  
  public function add_empty_rows($visits, $date_start, $date_finish)
  {
    $res_res = array();
    foreach ($visits as $person => $rows)
    {
      $date_range = DateFx :: date_range($date_start, $date_finish, 'Y-m-d');
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
  
  public function calculated_cell($cell_number, $coord_row)
  {
    $col = $this -> coord_col;
    switch ($cell_number)
    {
      case 4:
        $a = "{$col[3]}$coord_row";
        $b = "{$col[2]}$coord_row";
        return "=IF(OR(ISBLANK($a), ISBLANK($b)), \"\", $a-$b)";
      case 5:
        $a = "{$col[4]}$coord_row";
        return "=IFERROR(IF(ISBLANK($a), \"\", IF(HOUR($a) >= 1, \"1\", \"0\") & \":00:00\"), \"\")";
      case 6:
        $a = "{$col[4]}$coord_row";
        $b = "{$col[5]}$coord_row";
        return "=IF(OR(LEN($a) = 0, ISBLANK($b) = 0), \"\", $a-$b)";
    }
    return '';
  }
  
  public function sum_cell($col, $coord_row_from, $coord_row_to)
  {
    return "=SUM({$col}$coord_row_from:{$col}$coord_row_to)";
  }
  
  public function random_color()
  {
    return 'FF' . strtoupper(dechex(rand(150, 255) << 16 | rand(150, 255) << 8 | rand(150, 255)));
  }
  
  public function area($from, $to)
  {
    return ArrayFx :: first($this -> coord_col) . "$from:" . ArrayFx :: last($this -> coord_col) . "$to";
  }
  
  public function area_styles($bg_color)
  {
    return array(
      'fill' => array(
        'type' => PHPExcel_Style_Fill :: FILL_SOLID,
        'color' => array('argb' => $bg_color)
      ),
      'borders' => array(
        'inside'	=> array(
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('argb' => 'FF888888')
        )
      )
    );
  }
}
