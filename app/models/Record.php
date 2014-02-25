<?php

class Record
{
  const REST = 1;
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
    return self :: group_by_person($visits, $ppl);
  }
  
  public static function add_empty_rows($visits, $date_start, $date_finish)
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
  
  public static function prepare_for_export($visits)
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
    ksort($vis);
    return $vis;
  }
  
  protected static function array_array(&$array, $key)
  {
    if (!isset($array[$key])) $array[$key] = array();
  }
  
  protected static function work_time_array(&$visits, $format_diff = true)
  {
    foreach ($visits as $k => &$v)
      $v['DIFF'] = Record :: work_time($v['MIN_DATETIME'], $v['MAX_DATETIME'], $format_diff);
    return $visits;
  }
  
  public static function work_time($first, $last, $format_diff = true)
  {
    $first_dt = new DateTime($first);
    $last_dt = new DateTime($last);
    
    $diff = $last_dt -> diff($first_dt);
    if ($diff -> h * 60 + $diff -> i <= 60)
    {
      $subtracted = $diff;
      $rest = 0;
    }
    else
    {
      $minus_1h = $last_dt -> sub(DateInterval :: createFromDateString('1 hours'));
      $subtracted = $minus_1h -> diff($first_dt);
      $rest = self :: REST;
    }
    if ($format_diff)
    {
      $diff = $diff -> format('%h:%I');
      $subtracted = $subtracted -> format('%h:%I');
    }
    return array($diff, $rest, $subtracted);
  }
  
  public static function di_add($d, $d2)
  {
    $d->y += $d2->y;
    $d->m += $d2->m;
    $d->d += $d2->d;
    $d->h += $d2->h;
    $d->i += $d2->i;
    $d->s += $d2->s;
    if ($d->s >= 60) {$d->i += floor($d->s / 60); $d->s %= 60;}
    if ($d->i >= 60) {$d->h += floor($d->i / 60); $d->i %= 60;}
    return $d;
  }
}
