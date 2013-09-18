<?php

class Helper
{
  protected static $functions = null;
  
  public static function load()
  {
    self :: $functions = array();
    $pattern = 'app/helpers/*.php';
    $pattern_re = '~' . str_replace('*', '(.*)Helper', $pattern) . '~';
    foreach (glob($pattern) as $file)
    {
      preg_match($pattern_re, $file, $class_name);
      if (count($class_name) == 2)
      {
        $class_name = $class_name[1] . 'Helper';
        require $file;
        $class = new ReflectionClass($class_name);
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $m)
          self :: $functions[$m -> name] = $m;
      }
      else
        throw new E\Helper(t('exception.helper.filename', array('filename' => $file)));
    }
  }
  
  public function __call($name, $arguments)
  {
    if (isset(self :: $functions[$name]))
    {
      $class_name = self :: $functions[$name] -> class;
      $obj = new $class_name();
      return self :: $functions[$name] -> invokeArgs($obj, $arguments);
    }
    else
      throw new E\Helper(t('exception.helper.function_not_found', array('function' => $name)));
  }
}

Helper :: load();
