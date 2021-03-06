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
    
    $filter = array(
      'person_id' => $user_id, 'person_name' => $user_id ? $ppl[$user_id] : null,
      'date_start' => $date_start, 'date_end' => $date_end
    );
    
    $app -> render('enters/index.php', array('people' => $ppl, 'visits' => $vis, 'filter' => $filter));
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
    $vis = Record :: add_empty_rows($vis, $date_start, $date_end);
    $result = Record :: prepare_for_export($vis);
    
    if ($app -> conf('export'))
      echo json_encode(array('visits' => $result));
    else
    {
      $x = new PHPExcel();
      $x -> setActiveSheetIndex(0);
      $xs = $x -> getActiveSheet();
      
      $ex = new ExportRecord($xs);
      
      $ex -> put_data($result);
      
      $headers = $app -> response() -> headers();
      $headers -> set('Content-Type', 'application/vnd.openxmlformats-offedocument.spreadsheetml.sheet');
      $headers -> set('Content-Disposition', 'attachment;filename="'.$filename.'.xlsx"');
      //$headers -> set('Cache-Control: max-age=0');
      
      $w = PHPExcel_IOFactory :: createWriter($x, 'Excel2007');
      echo $w -> save('php://output');
    }
  }
}
