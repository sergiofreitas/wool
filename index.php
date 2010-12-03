<?php
require_once('components/wool.php');

// configs
Router::route('', 'home');
function home()
{
  Dao::init('sqlite:'.ROOT.'/mural.db');
  
  print_r(Dao::query('select * from t1'));
}

Wool::run();
