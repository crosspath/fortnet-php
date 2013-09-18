<?php

class GenericHelper
{
  public function select($hash, $selected_value, $options = null)
  {
    return $this -> content_tag('select', $options, $this -> options_for_select($hash, $selected_value));
  }
  
  public function options_for_select($hash, $selected_value)
  {
    $r = '<option value="">&ndash;</option>';
    foreach ($hash as $id => $name)
    {
      $r .= "<option value=\"$id\"" . ($id == $selected_value ? 'selected' : '') . ">$name</option>";
    }
    return $r;
  }
  
  public function content_tag($tag, $options = null, $content = '')
  {
    $opts = $options === null ? '' : ' ' . $this -> tag_options($options);
    return "<{$tag}{$opts}>{$content}</{$tag}>";
  }
  
  public function tag_options($options)
  {
    if (is_string($options))
      return $options;
    if (is_array($options))
    {
      $opts = array();
      foreach ($options as $k => $v)
        $opts[] = "$k=\"$v\"";
      return implode(' ', $opts);
    }
    if (is_null($options))
      return '';
    else
      throw new E\Helper(t('exception.helper.tag_options', array('arg' => $options)));
  }
}
