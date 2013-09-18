<?php

class Record
{
  protected $conditions = array();
  
  public static function visits($user_id, $date_start, $date_end, $ppl)
  {
    $db = Db :: get();
    $sql_date = "cast(r_dt as date)";
    $select = "$sql_date as THIS_DAY, R_DT as DATETIME, R_P as USER_ID, R_H as STATUS";
    list($params, $where) = self :: visits_filter($user_id, $date_start, $date_end);
    $order = "THIS_DAY, r_p, r_dt"; // по дате, по user_id, по времени
    $visits = $db -> select("SELECT $select FROM record $where ORDER BY $order", $params);
    return self :: group_visits($visits, $ppl);
  }
  
  protected static function visits_filter($user_id, $date_start, $date_end)
  {
    $dates = array();
    $params = array();
    if (!empty($date_start))
    {
      $dates[] = 'R_DT >= ?';
      $params[] = $date_start;
    }
    if (!empty($date_end))
    {
      $dates[] = 'R_DT <= ?';
      $params[] = $date_end . ' 23:59:59';
    }
    $dates = implode(' AND ', $dates);
    $conditions = array();
    $conditions[] = 'R_P > 0';
    if ($dates)
      $conditions[] = $dates;
    if ($user_id !== null)
    {
      $conditions[] = 'R_P = ?';
      $params[] = $user_id;
    }
    return array($params, "WHERE " . implode(' AND ', $conditions));
  }

  // надо отсортировать и сгруппировать посещения по дате, по ФИО, по времени
  protected static function group_visits($visits, $ppl)
  {
    $vis = array();
    foreach ($visits as $row)
    {
      $day = $row['THIS_DAY'];
      $name = $ppl[$row['USER_ID']];
      self :: array_array($vis, $day);
      self :: array_array($vis[$day], $name);
      $vis[$day][$name][] = $row;
    }
    foreach ($vis as $day => &$people)
      ksort($people);
    return $vis;
  }
  
  protected static function array_array(&$array, $key)
  {
    if (!isset($array[$key])) $array[$key] = array();
  }
}
