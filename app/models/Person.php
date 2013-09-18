<?php

class Person
{
  // список сотрудников
  public static function all()
  {
    $db = Db :: get();
    $people = $db -> select('SELECT P_ID as ID, P_FIO as NAME FROM people WHERE p_id > 0');
    // группировка: {id => row, ...}
    $ppl = array();
    foreach ($people as $row)
      $ppl[$row['ID']] = $row['NAME'];
    asort($ppl);
    return $ppl;
  }
}
