<?php

class Route
{
  public static function act($selector, $params = array())
  {
    list($controller, $action) = explode('#', $selector);
    $controller .= 'Controller';
    return function() use($controller, $action, $params)
    {
      $c = new $controller();
      echo $c -> $action($params);
    };
  }
}
