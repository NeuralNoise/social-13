<?
//������� - �����
pickup('includes', 'service', 'db', 'tables', 'uris', 'files', 'menus', 'templates', 'it', 'auth', 'sort', 'windows');

//�������� - �����
pickup('actions', 'show_positions', 'show_histories');

//�������������� ������ COOKIE
block("init_session");

//������������ � ���� ������
db_connect();

//prepared_uri
$prepared_uri=array();

//�����
$colors=array('red', 'green', 'blue', 'orange', 'brown', 'magenta', 'darkblue');

//���� ���� �� ��������
if(!isset($_SESSION['user'])){
	echo login_form();
	exit;
}

//������������ � ���� ������
db_connect();

//������ uri
uri_clean('add_position', 'add_document', 'edit_document', 'delete_document', 'save_document', 'show_originals', 'delete_file', 'edit_file',
			'show_histories', 'search_document', 'search_string', 'show_contragents', 'show_positions', 'edit_position', 'add_position', 'delete_position',
			'add_tag');

//Html ���� ��������
$html="";

//���������� �����
$html.=template_get('header');

//���������� ������� ����
$html.= menu_top();


//��� �������
$html.="<br/>".h1("����������")."<br/>";

//����������
$search_phrases=array('<h>����� � ��� � ���</h>', '�������', '�����', '',
						'<h>����������</h>', '����������� �����', '���������', '������ ������', '������� ����', '���������� ������', '������', '���� �������', '���������� ����������', '',
						'<h>�������� � ���������</h>', '�������', '����', '����������', '��������', '������� ������', '',
						'<h>��������/���/���������� � ���</h>', '�������', '���', '��������', '�����-��������', '', 
						'<h>��������������/�������/������</h>', '�������������', '', 
						'<h>���������</h>', '�������', '',
						'<h>������ ���������</h>', '������� �������', '������-����������', '���������',
						'���������� ����', '����-����', '������� �������', '����������',
						'������� ������������');
foreach($search_phrases as $id=>$phrase){
	if($phrase==""){
		$html.="<br/>";
	}elseif(strripos($phrase, "<h>")!==false){
		$html.="<b>".str_replace("</h>", '', str_replace("<h>", '', $phrase))."</b><br/>";
	}else{
		$html.="$phrase: ".db_easy_count("SELECT * FROM `positions` WHERE `name` LIKE '%".$phrase."%'")."&nbsp;&nbsp;&nbsp;<a href='".uri_make(array('show_positions'=>'yes', 'keyword'=>$phrase))."'>�����������</a><br/>";
	}
}

//��������� ��������
start_actions();

//���������� ������
$html.=template_get('footer');

//���� - � �������
echo $html;

//��������� ��������
function start_actions(){
	foreach($GLOBALS['actions_for_pickup'] as $id=>$file){
		$file="action_".$file;
		$file();
	}
}

//������� ����
function pickup(){
	//�������� ������ ������������ ������ � ����������
	$files=func_get_args();
	$folder=$files[0];
	unset($files[0]);
	
	if($folder=='actions'){
		$GLOBALS['actions_for_pickup']=$files;
	}
	
	//������� ���������� �����
	foreach($files as $id=>$file){
		require_once($_SERVER['DOCUMENT_ROOT']."/".$folder."/".$file.".php");
	}
}
?>