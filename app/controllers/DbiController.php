<?php

class DbiController
{
  // Время прихода и ухода, рабочее время для каждого выбранного человека в заданный период дат
  public function index()
  {
    $app = App :: get_app();
    $q = $app -> request() -> post('q');
    $db = Db :: get();
    $result = null;
    
    if (!empty($q) && strlen(trim($q)))
    {
      $k = (preg_match(
        /*'/[
          (\(?\s*)             # (SELECT ...
          ([\#(--)].*?[\r\n]+) # # comment ...
          (\/\*.*?\*\/)        # /* comment * /
        ]\s*
        SELECT/mix', $q*/
        '/^[\((\#.*?)(\/\*.*?\*\/)\s]*\s*SELECT/mix', $q
      ));
      if ($k)
        $result = $db -> select($q);
      else
        $result = print_r($db -> query($q), true);
    }
    
    $app -> render('dbi/index.php', array('q' => $q, 'result' => $result));
  }
}
