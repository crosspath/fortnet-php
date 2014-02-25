<?php

class ExtendedRoute extends \Slim\Route
{
  public function setCallable($callable)
  {
    if (is_string($callable))
    {
      list($controller, $action) = explode('#', $callable);
      $controller .= 'Controller';
      $callable = function() use($controller, $action)
      {
        $c = new $controller();
        echo call_user_func_array(array($c, $action), func_get_args());
      };
    }
    parent :: setCallable($callable);
  }
}
