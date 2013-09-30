<?php

class Record
{
  protected $conditions = array();
  
  public static function visits($user_id, $date_start, $date_end, $ppl)
  {
    $db = Db :: get();
    $sql_date = 'cast(r_dt as date)';
    $select = "$sql_date as THIS_DAY, R_DT as DATETIME, R_P as USER_ID, R_H as STATUS";
    list($params, $where) = self :: visits_filter($user_id, $date_start, $date_end);
    $order = 'THIS_DAY, r_p, r_dt'; // по дате, по user_id, по времени
    $visits = $db -> select("SELECT $select FROM record $where ORDER BY $order", $params);
    return self :: group_visits($visits, $ppl);
  }
  
  public static function visits_short_info($user_ids, $date_start, $date_end)
  {
    $db = Db :: get();
    $sql_date = 'cast(r_dt as date)';
    $dates = 'min(R_DT) as MIN_DATETIME, max(R_DT) as MAX_DATETIME';
    $select = "$sql_date as THIS_DAY, $dates, R_P as USER_ID";
    list($params, $where) = self :: visits_filter($user_ids, $date_start, $date_end);
    $order = 'THIS_DAY, USER_ID';
    $group = $order;
    $visits = $db -> select("SELECT $select FROM record $where GROUP BY $group ORDER BY $order", $params);
    return self :: work_time_array($visits);
  }
  
  public static function visits_group_by_person($user_id, $date_start, $date_end, $ppl)
  {
    $db = Db :: get();
    $sql_date = 'cast(r_dt as date)';
    $select = "$sql_date as THIS_DAY, min(R_DT) as MIN_DATETIME, max(R_DT) as MAX_DATETIME, R_P as USER_ID";
    list($params, $where) = self :: visits_filter($user_id, $date_start, $date_end);
    $order = 'USER_ID, THIS_DAY';
    $group = $order;
    $visits = $db -> select("SELECT $select FROM record $where GROUP BY $group ORDER BY $order", $params);
    self :: work_time_array($visits);
    return self :: group_by_person($visits, $ppl);
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
    if (!empty($user_id))
    {
      if (is_array($user_id))
      {
        $conditions[] = 'R_P in (?' . str_repeat(',?', count($user_id) - 1) . ')';
        $params = array_merge($params, $user_id);
      }
      else
      {
        $conditions[] = 'R_P = ?';
        $params[] = $user_id;
      }
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
  
  protected static function group_by_person($visits, $ppl)
  {
    $vis = array();
    foreach ($visits as $row)
    {
      $name = $ppl[$row['USER_ID']];
      self :: array_array($vis, $name);
      $vis[$name][] = $row;
    }
    foreach ($vis as $day => &$people)
      ksort($people);
    return $vis;
  }
  
  protected static function array_array(&$array, $key)
  {
    if (!isset($array[$key])) $array[$key] = array();
  }
  
  protected static function work_time_array(&$visits)
  {
    foreach ($visits as $k => &$v)
      $v['DIFF'] = Record :: work_time($v['MIN_DATETIME'], $v['MAX_DATETIME']);
    return $visits;
  }
  
  public static function work_time($first, $last)
  {
    $first_dt = new DateTime($first);
    $last_dt = new DateTime($last);
    
    $diff = $last_dt -> diff($first_dt);
    $total = $diff -> format('%h:%I');
    if ($diff -> h * 60 + $diff -> i <= 60)
    {
      $subtracted = $total;
      $rest = 0;
    }
    else
    {
      $minus_1h = $last_dt -> sub(DateInterval :: createFromDateString('1 hours'));
      $subtracted = $minus_1h -> diff($first_dt) -> format('%h:%I');
      $rest = 1;
    }
    return array($total, $rest, $subtracted);
  }
}
