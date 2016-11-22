<?php
//Если вход не выполнен
if(!isset($_SESSION['user'])){
	echo login_form();
	exit;
}
?>