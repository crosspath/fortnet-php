<?php

class DbiHelper
{
  public function dbi_result($result)
  {
    if (is_null($result) || empty($result))
      return 'None';
    elseif (is_string($result))
      return $result;
    elseif (is_array($result))
    {
      $r = '<table><thead><tr><th>' . implode('</th><th>', array_keys($result[0])) . '</th></tr></thead><tbody>';
      foreach ($result as $row):
        $r .= '<tr><td>' . implode('</td><td>', $row) . '</td></tr>';
      endforeach;
      $r .= '</tbody></table>';
      return $r;
    }
  }
}
