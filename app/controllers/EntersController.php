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
      'filter' => array('person' => $user_id, 'date_start' => $date_start, 'date_end' => $date_end)
    ));
  }
}
