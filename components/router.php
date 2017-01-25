<?php

class Router
{
  static private $routes;

  static public function redirect($regex, $file)
  {
    self::$routes[] = array('regex'=>$regex, 'file'=>$file);
  }

  static public function route($regex, $callback)
  {
    self::$routes[] = array('regex'=>$regex, 'callback'=>$callback);
  }

  static public function dispatch($url)
  {
    $action = self::matchs($url);
    if ( $action )
      return call_user_func_array($action['function'], $action['args']);
  }

  static public function matchs($url)
  {
    if ( !$url ) { $url = '/'; }
    $url = ($url[0] == '/') ? substr($url, 1) : $url;
    foreach( self::$routes as $route ){
      $regex = $route['regex'];
      if ( preg_match("@^$regex@", $url, $params) ){
        if ( isset($route['file']) ){
          self::$routes = array();
          Wool::load($route['file']);
          return self::dispatch(preg_replace("@^$regex@", '', $url));
        } else {
          $args = array();

          if ( strpos($route['callback'], '::') ){
            $method = explode('::', $route['callback']);
            $r_fn = new ReflectionMethod($method[0], $method[1]);
          } else {
            $r_fn = new ReflectionFunction($route['callback']);
          }
          
          foreach($r_fn->getParameters() as $r_pr){
            $name = $r_pr->getName();
            if ( isset($params[$name]) )
                $args[] = $params[$name];
          }
          return array('function'=>$route['callback'], 'args'=>$args);
        }
      }
    }
  }
}

function router_bind($params){
  Wool::trigger('dispatch', $params);
  return Router::dispatch($params['url']);
}

Wool::bind('init', 'router_bind');
