<?php
//���� ���� �� ��������
if(!isset($_SESSION['user'])){
	echo login_form();
	exit;
}
?>