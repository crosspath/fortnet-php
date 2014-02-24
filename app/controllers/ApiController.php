<?php

class ApiController
{
  // Время прихода и ухода, рабочее время для каждого выбранного человека в заданный период дат
  public function visits()
  {
    $app = App :: get_app();
    $ = $app -> request();
    
    $date_start = $r -> get('date_start');
    $date_end = $r -> get('date_end');
    $user_ids = $r -> get('person');
    
    if (is_null($date_start) || is_null($date_end) || is_null($user_ids))
      return $this -> wrap('error', 'Expected arguments: date_start, date_end, person');
    
    $vis = Record :: visits_short_info(self :: split($user_ids, ','), $date_start, $date_end);
    return $this -> wrap(__FUNCTION__, $vis);
  }
  
  public function search_people()
  {
    $app = App :: get_app();
    
    $names = $app -> request() -> get('people');
    if (!$names)
      return $this -> wrap('error', 'Expected arguments: people');
    
    $ids = array();
    foreach (self :: split($names, ',') as $name)
      $ids[$name] = Person :: find_by_name($name);
    return $this -> wrap(__FUNCTION__, $ids);
  }
  
  protected static function split($a, $d)
  {
    $b = explode($d, $a);
    foreach ($b as &$v)
      $v = trim($v);
    return $b;
  }
  
  protected function wrap($func, $data)
  {
    App :: get_app() -> response() -> headers() -> set('Content-Type', 'application/json');
    return json_encode(array($func => $data));
  }
}
