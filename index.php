<?php
require_once('components/wool.php');

class Actions {
  static function login(){
    echo 'login page';
  }

  static function home(){
    echo 'home page';
  }
}

// configs
Router::route('login', 'Actions::login');
Router::route('', 'Actions::home');

Wool::run();
