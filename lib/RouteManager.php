<?php

class RouteManager
{
  protected static $route = 'ExtendedRoute';
  protected $backend = null;
  
  public function __construct(\Slim\Slim $backend)
  {
    $this -> backend = $backend;
  }
  
  protected function mapRoute($args)
  {
    $pattern = array_shift($args);
    $callable = array_pop($args);
    $route = new self :: $route($pattern, $callable); // changed line
    $this -> backend -> router -> map($route);
    if (count($args) > 0)
      $route->setMiddleware($args);

    return $route;
  }
  
  public function map() { return $this->mapRoute(func_get_args()); }
  
  public function get()
  {
    $args = func_get_args();
    return $this->mapRoute($args)->via(\Slim\Http\Request::METHOD_GET, \Slim\Http\Request::METHOD_HEAD);
  }
  
  public function post()
  {
    $args = func_get_args();
    return $this->mapRoute($args)->via(\Slim\Http\Request::METHOD_POST);
  }
  
  public function put()
  {
    $args = func_get_args();
    return $this->mapRoute($args)->via(\Slim\Http\Request::METHOD_PUT);
  }
  
  public function patch()
  {
    $args = func_get_args();
    return $this->mapRoute($args)->via(\Slim\Http\Request::METHOD_PATCH);
  }
  
  public function delete()
  {
    $args = func_get_args();
    return $this->mapRoute($args)->via(\Slim\Http\Request::METHOD_DELETE);
  }
  
  public function options()
  {
    $args = func_get_args();
    return $this->mapRoute($args)->via(\Slim\Http\Request::METHOD_OPTIONS);
  }
  
  public function any()
  {
    $args = func_get_args();
    return $this->mapRoute($args)->via("ANY");
  }
}
