<?php

use Civi\Cv\Bootstrap;
use CRM\CivixBundle\Utils\Path;
use Symfony\Component\Templating\PhpEngine;
use Symfony\Component\Templating\TemplateNameParser;
use Symfony\Component\Templating\Loader\FilesystemLoader;

class Civix {

  protected static $cache;

  public static function boot($options = []) {
    if (!isset(self::$cache['boot'])) {
      $cwd = getcwd();
      // TODO: It might be better for civix commands to use cv's BootTrait.
      if (!empty(getenv('CIVICRM_BOOT'))) {
        \Civi\Cv\CmsBootstrap::singleton()
          ->addOptions($options)
          ->bootCms()
          ->bootCivi();
      }
      else {
        $options = array_merge(['prefetch' => FALSE], $options);
        Bootstrap::singleton()->boot($options);
        \CRM_Core_Config::singleton();
        \CRM_Utils_System::loadBootStrap([], FALSE);
      }
      chdir($cwd);
      self::$cache['boot'] = 1;
    }
  }

  /**
   * Get a reference to the API class.
   *
   * Pre-requisite: call boot() to startup CiviCRM.
   *
   * @return \civicrm_api3
   */
  public static function api3() {
    if (!isset(self::$cache['civicrm_api3'])) {
      if (!stream_resolve_include_path('api/class.api.php')) {
        throw new \RuntimeException("Booted CiviCRM, but failed to find 'api/class.api.php'");
      }
      require_once 'api/class.api.php';
      self::$cache['civicrm_api3'] = new \civicrm_api3();
    }
    return self::$cache['civicrm_api3'];
  }

  /**
   * @return \Symfony\Component\Templating\EngineInterface
   */
  public static function templating() {
    if (!isset(self::$cache['templating'])) {
      $loader = new FilesystemLoader(static::appDir('/src/CRM/CivixBundle/Resources/views/Code/%name%'));
      self::$cache['templating'] = new PhpEngine(new TemplateNameParser(), $loader);
    }
    return self::$cache['templating'];
  }

  /**
   * Read any config data (~/.civix/civix.ini).
   *
   * @return array
   */
  public static function config() {
    if (!isset(self::$cache['config'])) {
      $file = self::configDir()->string('civix.ini');
      if (file_exists($file)) {
        self::$cache['config'] = parse_ini_file($file, TRUE);
      }
      else {
        self::$cache['config'] = [];
      }
    }
    return self::$cache['config'];
  }

  /**
   * Get the root path of the civix application.
   *
   * @param string[] $parts
   *   Optional list of sub-paths
   *   Ex: ['src', 'CRM', 'CivixBundle']
   * @return \CRM\CivixBundle\Utils\Path
   *   Ex: '/home/myuser/src/civix'
   *   Ex: 'phar://usr/local/bin/civix.phar'
   */
  public static function appDir(...$parts): Path {
    return Path::for(dirname(__DIR__), ...$parts);
  }

  /**
   * @param string[] $parts
   * @return \CRM\CivixBundle\Utils\Path
   */
  public static function configDir(...$parts): Path {
    if (!isset(self::$cache['configDir'])) {
      $homes = [
        getenv('HOME'), /* Unix */
        getenv('USERPROFILE'), /* Windows */
      ];
      foreach ($homes as $home) {
        if (!empty($home)) {
          self::$cache['configDir'] = new Path($home . '/.civix');
          break;
        }
      }
      if (empty($home)) {
        throw new \RuntimeException('Failed to locate home directory. Please set HOME (Unix) or USERPROFILE (Windows).');
      }
    }
    return Path::for(self::$cache['configDir'], ...$parts);
  }

  /**
   * @param string[] $parts
   * @return \CRM\CivixBundle\Utils\Path
   */
  public static function cacheDir(...$parts): Path {
    if (!isset(self::$cache['cacheDir'])) {
      self::$cache['cacheDir'] = self::configDir()->path('cache');
    }
    return Path::for(self::$cache['cacheDir'], ...$parts);
  }

  /**
   * Get the root path of the extension being developed.
   *
   * @param string[] $parts
   *   Optional list of sub-paths
   *   Ex: ['xml', 'Menu', 'foo.xml']
   * @return \CRM\CivixBundle\Utils\Path
   *   Ex: '/var/www/example.com/files/civicrm/ext/foobar'
   */
  public static function extDir(...$parts): Path {
    $cwd = rtrim(getcwd(), '/');
    if (file_exists("$cwd/info.xml")) {
      return Path::for($cwd, ...$parts);
    }
    else {
      throw new \RuntimeException("Failed to find \"info.xml\" ($cwd/). Are you running in the right directory?");
    }

  }

  /**
   * @return \Mixlib
   */
  public static function mixlib(): Mixlib {
    if (!isset(self::$cache[__FUNCTION__])) {
      if (!class_exists('Mixlib')) {
        // For some reason, autoloading rule doesn't for this doesn't survive box/php-scoper/phar transofrmation.
        require_once static::appDir('extern/src/Mixlib.php');
      }
      self::$cache[__FUNCTION__] = new Mixlib(static::appDir('extern/mixin'));
    }
    return self::$cache[__FUNCTION__];
  }

  /**
   * @return array
   */
  public static function mixinBackports(): array {
    if (!isset(self::$cache[__FUNCTION__])) {
      self::$cache[__FUNCTION__] = require \CRM\CivixBundle\Application::findCivixDir() . '/mixin-backports.php';
    }
    return self::$cache[__FUNCTION__];
  }

  /**
   * @return \CRM\CivixBundle\UpgradeList
   */
  public static function upgradeList(): \CRM\CivixBundle\UpgradeList {
    if (!isset(self::$cache[__FUNCTION__])) {
      self::$cache[__FUNCTION__] = new \CRM\CivixBundle\UpgradeList();
    }
    return self::$cache[__FUNCTION__];
  }

}
