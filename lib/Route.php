<?php

class Route
{
  public static function act($selector)
  {
    list($controller, $action) = explode('#', $selector);
    $controller .= 'Controller';
    return function() use($controller, $action)
    {
      $c = new $controller();
      echo call_user_func_array(array($c, $action), func_get_args());
    };
  }
}
