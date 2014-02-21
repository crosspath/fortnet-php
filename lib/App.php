<?php

class App
{
  private static $instance = null;
  private $backend = null;
  const DEBUG = true;
  
  private function __construct($params = array())
  {
    self :: register_autoloader();
    \Slim\Slim :: registerAutoloader();
    $this -> backend = new \Slim\Slim(array(
      'debug' => self :: DEBUG,
      'templates.path' => 'app/views',
      'view' => 'ExtendedView'
    ));
    Text :: read();
  }
  
  public static function get_app()
  {
    if (!self :: $instance)
      self :: $instance = new self();
    return self :: $instance;
  }
  
  private static function register_autoloader()
  {
    spl_autoload_register('App::autoload');
  }
  
  public static function autoload($class_name)
  {
    $special = array('Helper' => 'app/helpers', 'Controller' => 'app/controllers');
    foreach ($special as $k => $path)
    {
      if (self :: ends_on($class_name, $k) && self :: autoload_file($path, $class_name))
          return true;
    }
    $folders = array('lib', 'vendor', 'app/models');
    foreach ($folders as $f)
    {
      if (self :: autoload_file($f, $class_name))
        return true;
    }
    return false;
  }
  
  protected static function autoload_file($folder, $class_name)
  {
    $file_name = "$folder/$class_name.php";
    if (file_exists($file_name))
    {
      require $file_name;
      return true;
    }
    return false;
  }
  
  // delegate to $backend
  public function __call($name, $arguments)
  {
    $callback = array($this -> backend, $name);
    if (!is_callable($callback))
      throw new E\Callable(t('exception.callable.not_callable', array('function' => $name, 'obj' => 'Slim\Slim')));
    return call_user_func_array($callback, $arguments);
  }
  
  public static function ends_on($string, $substring)
  {
    $r = mb_strrpos($string, $substring);
    return $r === false ? false : ($r + mb_strlen($substring) == mb_strlen($string));
  }
}
