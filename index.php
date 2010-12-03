<?php
require_once('components/wool.php');

// configs
Router::route('', 'home');
function home()
{
  Dao::init('sqlite:'.ROOT.'/teste.db');
  Dao::execute('Create Table t1 (id integer primary key autoincrement, name varchar(100))');
  echo 'abc';
}

Wool::run();
