<?php

class ExtendedView extends \Slim\View
{
  protected function render($template)
  {
    $templatePathname = $this->getTemplatePathname($template);
    if (!is_file($templatePathname))
      throw new \RuntimeException("View cannot render `$template` because the template does not exist");
    extract($this->data->all());
    ob_start();
    
    // added:
    $h = new Helper();
    
    require $templatePathname;
    
    return ob_get_clean();
  }
}
