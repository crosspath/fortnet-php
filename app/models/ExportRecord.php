<?php

class ExportRecord
{
  const TOP_ROW = 1;
  public $xs = null;
  
  public function __construct(PHPExcel_Worksheet $xs)
  {
    $this -> xs = $xs;
  }
  
  public function put_columns()
  {
    $columns = array(
      t('app.export.col_date'),
      t('app.export.col_name'),
      t('app.export.col_in'),
      t('app.export.col_out'),
      t('app.export.col_diff'),
      t('app.export.col_lunch'),
      t('app.export.col_res'),
      t('app.export.col_fulltime'),
      t('app.export.col_salary')
    );
    $top_row = self :: TOP_ROW;
    $this -> xs -> fromArray($columns, null, 'A1');
  }
  
  public function prepare_first_4_fields($visits)
  {
    $res = array();
    foreach ($visits as $person => $rows)
    {
      $record = array(null, $person);
      $res[$person] = array();
      foreach ($rows as $row)
      {
        $record[0] = DateFx :: day($row['THIS_DAY']);
        $record[2] = isset($row['MIN_DATETIME']) ? DateFx :: time($row['MIN_DATETIME']) : '';
        $record[3] = isset($row['MAX_DATETIME']) ? DateFx :: time($row['MAX_DATETIME']) : '';
        $res[$person][] = $record;
      }
    }
    return $res;
  }
  
  public function put_data($result)
  {
    $counter = self :: TOP_ROW + 1;
    
    $this -> xs -> getDefaultStyle() -> applyFromArray(
      array('font' => array('name' => 'Arial', 'size' => 11))
    );
    for ($col = ord('A'), $c = ord('I'); $col < $c; $col++)
      $this -> xs -> getColumnDimension(chr($col)) -> setAutoSize(true);
    $this -> put_columns();
    
    foreach ($result as $person => $rows)
    {
      $first_row_for_person = $counter;
      $res = array();
      $res[] = $this -> info_row($person, $first_row_for_person);
      $counter++;
      
      foreach ($rows as $row)
      {
        $res[] = $this -> calc_row($row, $first_row_for_person, $counter);
        $counter++;
      }
      $res[] = $this -> calc_sum_row($person, $first_row_for_person, $counter-1);
      
      $this -> xs -> fromArray($res, null, "A$first_row_for_person");
      $this -> add_styles($first_row_for_person, $counter);
      $counter++;
    }
    
    $this -> xs -> setAutoFilter($this -> xs -> calculateWorksheetDimension());
  }
  
  public function info_row($person, $c)
  {
    return array(
      '', $person, t('app.export.salary'), '0', t('app.export.salary_hour'), "=IFERROR(D$c/22/8, 0)", '', '', ''
    );
  }
  
  public function calc_row($row, $first_row_for_person, $counter)
  {
    $row[] = $this -> calc_diff($counter);
    $row[] = $this -> calc_rest($counter);
    $row[] = $this -> calc_res($counter);
    $row[] = '="8:00:00"-"0"';
    $row[] = $this -> calc_salary($first_row_for_person, $counter);
    return $row;
  }
  
  public function calc_sum_row($person, $from, $to)
  {
    return array(
      '', $person, '', '', '', t('app.export.sum'),
      $this -> calc_sum('G', $from, $to),
      $this -> calc_sum('H', $from, $to),
      $this -> calc_sum('I', $from, $to)
    );
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
  
  public function calc_diff($counter)
  {
    $a = "D$counter";
    $b = "C$counter";
    return "=IF(OR(LEN($a)=0, LEN($b)=0), \"\", $a-$b)";
  }
  
  public function calc_rest($counter)
  {
    $a = "E$counter";
    return "=IFERROR(IF(LEN($a)=0, \"\", IF(HOUR($a) >= 1, \"1\", \"0\") & \":00:00\"), \"\")";
  }
  
  public function calc_res($counter)
  {
    $a = "E$counter";
    $b = "F$counter";
    return "=IF(OR(LEN($a) = 0, LEN($b) = 0), \"\", $a-$b)";
  }
  
  public function calc_sum($col, $from, $to)
  {
    return "=SUM({$col}$from:{$col}$to)";
  }
  
  public function calc_salary($first_row_for_person, $counter)
  {
    $base = '$F$'.$first_row_for_person;
    $time = "G$counter";
    return "=IF(OR(ISBLANK($time),$time=\"\"),\"\",$base*(HOUR($time)+MINUTE($time)/60+SECOND($time)/3600))";
  }
  
  public function random_color()
  {
    return 'FF' . strtoupper(dechex(rand(150, 255) << 16 | rand(150, 255) << 8 | rand(150, 255)));
  }
  
  public function area_styles($bg_color)
  {
    return array(
      'fill' => array(
        'type' => PHPExcel_Style_Fill :: FILL_SOLID,
        'color' => array('argb' => $bg_color)
      ),
      'borders' => array(
        'allborders'	=> array(
          'style' => PHPExcel_Style_Border::BORDER_THIN,
          'color' => array('argb' => 'FF888888')
        )
      )
    );
  }
  
  public function add_styles($from, $to)
  {
    $only_color = $this -> area_styles($this -> random_color());
    $this -> xs -> getStyle("A$from:I$to") -> applyFromArray($only_color);
    
    $ffrom = $from+1;
    $tto = $to-1;
    $time = array('numberformat' => array('code' => '[h]:mm:ss'));
    $this -> xs -> getStyle("C$ffrom:H$tto") -> applyFromArray($time);
    $this -> xs -> getStyle("G$to:H$to") -> applyFromArray($time);
    
    $money = array('numberformat' => array('code' => '#,##0.00"р"\.'));
    $this -> xs -> getStyle("I$from:I$to") -> applyFromArray($money);
  }
}
