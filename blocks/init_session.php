<?php
//Время жизни сессии
$session_lifetime=86400;

//Задаем директивы php.ini
//ini_set('session.gc_maxlifetime', $session_lifetime);
//ini_set('session.cookie_lifetime', $session_lifetime);

//Время жизни должно задаваться до вызова session_start()
session_set_cookie_params($session_lifetime);
//Начинаем сессию и задаем ее время жизни 	
session_start();
setcookie(session_name(),session_id(),time()+$session_lifetime);
?>