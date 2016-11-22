<?
//Добавление продукта с низким FODMAP
function action_add_low_fodmap(){
	if(@$_GET['add_low_fodmap']=="yes"){
		if(isset($_POST['name'])){
			if(get_user_group($_SESSION['user'])=="writer"){
				db_connect("diet");
				$q=db_query("INSERT INTO `low_fodmaps`
								SET	`name`='{$_POST['name']}'
						");
				$new_document_id=db_insert_id($q);
				header("location: ".uri_make(array('UriScript'=>'diet.php', 'UriClean'=>'DeleteAllArguments')));
			}
		}else{
			$GLOBALS['html'].=template_get('diet/add_low_fodmap', array('action'=>uri_make(array('UriScript'=>'diet.php'))));	
		}
	}
}
?>