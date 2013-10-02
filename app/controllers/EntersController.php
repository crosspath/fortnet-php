<?php

class EntersController
{
  public function index()
  {
    $app = App :: get_app();
    
    // фильтр дат - если не установлен, то с начала месяца
    $date_start = $app -> request() -> get('date_start');
    if ($date_start === null)
      $date_start = date('Y-m-d', mktime(0, 0, 0, date('n'), 1, date('Y')));
    
    $date_end = $app -> request() -> get('date_end');
    
    $user_id = $app -> request() -> get('person');
    if (!$user_id)
      $user_id = null;
    
    $ppl = Person :: all();
    $vis = Record :: visits($user_id, $date_start, $date_end, $ppl);
    
    $app -> render('enters/index.php', array(
      'people' => $ppl,
      'visits' => $vis,
      'filter' => array('person' => $user_id, 'date_start' => $date_start, 'date_end' => $date_end),
      'fact_filter' => $this -> fact_filter($user_id, $ppl, $vis)
    ));
  }
  
  public function export($filename)
  {
    $app = App :: get_app();
    
    $f = explode('---', $filename);
    $date_start = $f[0];
    $date_end = $f[1];
    $person = !empty($f[2]) ? $f[2] : null;
    if (!empty($person))
    {
      $user_id = Person :: find_by_name($person);
      $user_id = $user_id['ID'];
    }
    else
      $user_id = null;
    
    $vis = Record :: visits_group_by_person($user_id, $date_start, $date_end, Person :: all());
    
    $x = new PHPExcel();
    $x -> setActiveSheetIndex(0);
    $xs = $x -> getActiveSheet();
    
    $coord_col = array();
    $top_row = ExportRecord :: $top_row;
    foreach (ExportRecord :: columns() as $key => $c)
    {
      $col = ExportRecord :: coord_column($key);
      $xs -> SetCellValue("{$col}{$top_row}", $c);
      $xs -> getColumnDimension($col) -> setAutoSize(true);
      $coord_col[] = $col;
    }
    
    $vis = ExportRecord :: add_empty_rows($vis, $date_start, $date_end);
    
    $result = ExportRecord :: table($vis);
    $counter = ExportRecord :: $top_row + 1;
    
    foreach ($result as $record)
    {
      foreach ($record as $k => $value)
      {
        $cell = $coord_col[$k] . $counter;
        $xs -> SetCellValue($cell, $record[$k]);
        if (preg_match('/:.+:/', $record[$k]))
          $xs -> getStyle($cell) -> getNumberFormat() -> setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_TIME4);
      }
      $counter++;
    }
    
    $headers = $app -> response() -> headers();
    $headers -> set('Content-Type', 'application/vnd.openxmlformats-offedocument.spreadsheetml.sheet');
    $headers -> set('Content-Disposition', 'attachment;filename="'.$filename.'.xlsx"');
    //$headers -> set('Cache-Control: max-age=0');
    
    $w = PHPExcel_IOFactory :: createWriter($x, 'Excel2007');
    echo $w -> save('php://output');
  }
  
  protected function fact_filter($user_id, $people, $visits)
  {
    $days = array_keys($visits);
    $date_start = array_shift($days);
    $date_end = empty($days) ? $date_start : array_pop($days);
    return array(
      'person' => empty($people[$user_id]) ? '' : $people[$user_id],
      'person_id' => $user_id, 
      'date_start' => $date_start,
      'date_end' => $date_end
    );
  }
}
