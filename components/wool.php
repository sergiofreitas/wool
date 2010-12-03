<?php

define('DS', DIRECTORY_SEPARATOR);
define('ROOT', dirname(dirname(__FILE__)));
define('EXT', '.php');

class Wool
{
  static private $configs;
  static private $events;

  static public function load($file)
  {
    $default_path = array(ROOT.'/components', ROOT);
    $dirs = array_merge($default_path, (array) self::config('libs')); 
    foreach($dirs as $dir){
      $full_path = str_replace(DS.DS, DS, $dir.DS.$file.EXT);
      if ( file_exists($full_path) && is_readable($full_path) )
        return include_once($full_path);
    }
  }

  static public function config($key, $value=null)
  {
    if ( $value === null )
    {
      return self::_config_get($key);
    } else {
      $old = self::$configs;
      $new = self::_config_set(explode('.',$key), $value);
      self::$configs = self::_merge_array($old, $new); 
    }
  }

  static private function _config_get($key)
  {
    $parts = explode('.', $key);
    $it = self::$configs;
    foreach ($parts as $part) {
      if ( isset($it[$part]) )
        $it = $it[$part];	
      else 
	return null;
      }
    return $it;
  }

  static private function _config_set($parts, $value)
  {
    $part = array_shift($parts);
    if ( !count($parts) )
      $result = $value;
    else
      $result = self::_config_set($parts, $value);
    return array( $part => $result );
  }

  static private function _merge_array($array1, $array2)
  {
    foreach( $array2 as $key => &$value ){
      if ( is_array($value) && isset($array1[$key]) && is_array($array1[$key]) )
        $array1[$key] = self::_merge_array($array1[$key], $value);
      else
        $array1[$key] = $value;
    }
    return $array1;
  }

  static public function bind($event, $callback)
  {
    self::$events[$event][] = $callback;
  }

  static public function trigger($event, $params=array())
  {
    $params['chain'] = null;
    foreach( self::$events[$event] as $callback ){
      $params['chain'] = call_user_func($callback, $params);
    }
    return $params['chain'];
  }

  static public function run()
  {
    self::trigger('init', array('url'=>$_SERVER['PATH_INFO']));
    self::trigger('shutdown');
  }
}

/* autoload function */
spl_autoload_register(function($classname){
  $file = strtolower(str_replace('_',DS, $classname));
  Wool::load($file);
});

/*
 * Wool::load(); 
 *
 * Wool::config('key', 'value');
 *
 * Wool::bind('event','callback');
 * Wool::trigger('event', ...);
 *
 * Router::add('/<controller>', '<file>.<callback>');
 * Router::add('/<controller>', '<file>');
 * Router::add('/', 'callback');
 */
