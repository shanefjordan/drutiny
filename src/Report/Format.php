<?php

namespace Drutiny\Report;

use Drutiny\Profile;
use Drutiny\Target\Target;
use Symfony\Component\Console\Output\OutputInterface;
use Drutiny\Config;

abstract class Format {

  /**
   * The format the object applies to.
   *
   * @var string
   */
  protected $format;

  protected $output = 'stdout';

  abstract public function __construct($options);

  public static function create($format, $options)
  {
    $formats = Config::get('Format');
    if (!isset($formats[$format])) {
      throw new \InvalidArgumentException("Reporting format '$format' is not supported.");
    }
    return new $formats[$format]($options);
  }

  /**
   * Get the profile title.
   */
  public function getFormat()
  {
    return $this->format;
  }

  /**
   * Set the title of the profile.
   */
  protected function setFormat($format)
  {
    $this->format = $format;
    return $this;
  }

  final public function render(Profile $profile, Target $target, array $results)
  {
    if (count($results) == 1) {
      $result = reset($results);
      // @var array
      $variables = $this->preprocessResult($profile, $target, $result);

      // @var string
      $renderedOutput = $this->renderResult($variables);
    }
    else {
      // @var array
      $variables = $this->preprocessMultiResult($profile, $target, $results);

      // @var string
      $renderedOutput = $this->renderMultiResult($variables);
    }

    if ($this->getOutput() instanceof OutputInterface) {
      $this->getOutput()->writeln($renderedOutput);
    }
    else {
      file_put_contents($this->getOutput(), $renderedOutput);
    }
  }

  abstract protected function preprocessResult(Profile $profile, Target $target, array $result);
  abstract protected function preprocessMultiResult(Profile $profile, Target $target, array $results);

  abstract protected function renderResult(array $variables);
  abstract protected function renderMultiResult(array $variables);

  /**
   * Render an HTML template.
   *
   * @param string $tpl
   *   The name of the .html.tpl template file to load for rendering.
   * @param array $render
   *   An array of variables to be used within the template by the rendering engine.
   *
   * @return string
   */
  public function renderTemplate($tpl, array $render) {
    $registry = new \Drutiny\Registry();
    $loader = new \Twig_Loader_Filesystem($registry->templateDirs());
    $twig = new \Twig_Environment($loader, array(
      'cache' => sys_get_temp_dir() . '/drutiny/cache',
      'auto_reload' => TRUE,
    ));

    $template = $twig->load($tpl . '.' . $this->getFormat() . '.twig');
    $contents = $template->render($render);
    return $contents;
  }

  /**
   * Get the profile title.
   */
  public function getOutput()
  {
    return $this->output;
  }

  /**
   * Set the title of the profile.
   */
  public function setOutput($filepath = 'stdout')
  {
    if ($filepath != 'stdout' && !($filepath instanceof OutputInterface) && !file_exists(dirname($filepath))) {
      throw new \InvalidArgumentException("Cannot write to $filepath. Parent directory doesn't exist.");
    }
    $this->output = $filepath;
    return $this;
  }
}

 ?>
