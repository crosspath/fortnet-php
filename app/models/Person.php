<?php

class Person
{
  // список сотрудников
  public static function all()
  {
    $db = Db :: get();
    $people = $db -> select('SELECT P_ID as ID, P_FIO as NAME FROM people WHERE p_id > 0');
    return self :: group_by_id($people);
  }
  
  public static function find_by_name($name)
  {
    $db = Db :: get();
    $query = 'SELECT FIRST 1 P_ID as ID, P_FIO as NAME FROM people WHERE P_FIO LIKE ?';
    $people = $db -> select($query, array("%$name%"));
    return $people ? $people[0] : null;
  }
  
  protected static function group_by_id($rows)
  {
    // группировка: {id => row, ...}
    $ppl = array();
    foreach ($rows as $row)
      $ppl[$row['ID']] = $row['NAME'];
    asort($ppl);
    return $ppl;
  }
}
