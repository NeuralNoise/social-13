<?php
//����� ����� ������
$session_lifetime=86400;

//������ ��������� php.ini
//ini_set('session.gc_maxlifetime', $session_lifetime);
//ini_set('session.cookie_lifetime', $session_lifetime);

//����� ����� ������ ���������� �� ������ session_start()
session_set_cookie_params($session_lifetime);
//�������� ������ � ������ �� ����� ����� 	
session_start();
setcookie(session_name(),session_id(),time()+$session_lifetime);
?>