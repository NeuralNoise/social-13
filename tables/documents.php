<?php
//Определяем будущие массивы
$patents=array();
$table_matrix_appearance=array();
$table_matrix_columns=array();

$filter_document=$GLOBALS['filter_document'];

$where="";
if($filter_document!="all"){
	$where="WHERE `document_id`=".$filter_document;
}

//Получаем матрицу из базы данных
$a=db_query("SELECT *
				FROM `positions`
				$where");
				
while($position=db_fetch($a))
{
    $id=$position['id'];
    $positions[$id]['id']=$id;
	if($filter_document=="all") $positions[$id]['document']=db_easy("SELECT `name` FROM `documents` WHERE `id`=".$position['document_id'])['name'];
	$positions[$id]['name']=$position['name'];
	$positions[$id]['serial']=$position['serial'];
	$position['uniq_id']==0 ? $positions[$id]['uniq_id']='' : $positions[$id]['uniq_id']=$position['uniq_id'];

}

//Подготавливаем первую строку для таблицы
if($filter_document=="all") $table_matrix[0]['document']='Документ покупки';
$table_matrix[0]['name']='Наименование';
$table_matrix[0]['serial']='Серийник';
$table_matrix[0]['uniq_id']='Внутренний №';
$table_matrix[0]['history']='';
$table_matrix[0]['delete']='';


if(get_user_group($_SESSION['user'])=="writer") $table_matrix[0]['delete']='';

//Подготавливаем остальные строки для таблицы
if(count($positions)>0){
    foreach($positions as $id=>$position)
    {
        //Сами строки
		$table_matrix[$id]['id']=$position['id'];
		if($filter_document=="all") $table_matrix[$id]['document']=$position['document'];
		$table_matrix[$id]['name']=$position['name'];
		$table_matrix[$id]['serial']=$position['serial'];
		$table_matrix[$id]['uniq_id']=$position['uniq_id'];
		
		//История
		$table_matrix[$id]['history']="История";
		$table_matrix_links[$id]['history']['href']=uri_make(array('show_histories'=>$id));
		$table_matrix_appearance[$id]['history']['style']="color:blue;";

		//Удалить		
		if(get_user_group($_SESSION['user'])=="writer"){
			$table_matrix[$id]['delete']="Удалить";
			$table_matrix_links[$id]['delete']['href']=uri_make(array('delete_position'=>$id));
			$table_matrix_appearance[$id]['delete']['style']="color:red;";
			$table_matrix_appearance[$id]['delete']['onclick']="if(!confirm(\"Удалить?\")) return false;";
		}
		
		
    }
}

//Задаем первую строку таблицы
$table['row_first']=$table_matrix[0];

//Задаем матрицу таблицы
unset($table_matrix[0]);
$table['matrix']=$table_matrix;

$table_matrix_columns['id']['input_type']='hidden';
//$table_matrix_columns['date_buy']['type']="date";

//Ширина столбцов (раздвигается полями ввода)
//$table_matrix_columns['date_buy']['width']='80px';

//Ширина полей ввода
$table_matrix_columns['document']['input_width']='300px';
$table_matrix_columns['name']['input_width']='500px';
$table_matrix_columns['serial']['input_width']='150px';
$table_matrix_columns['uniq_id']['input_width']='100px';
$table_matrix_columns['history']['input_width']='200px';

$table_rows['height']="28px";


//Задаем матрицу внешнего вида таблицы
$table['matrix_appearance']=$table_matrix_appearance;

//Задаем имя таблицы
$table['header']="Документ покупки";


//Задаем название счетчика
$table['counter']="Записей";

//Свойства колонок
$table['columns']=$table_matrix_columns;

//Свойства строк
$table['rows']=$table_rows;

//Содержит адреса ссылок
$table['matrix_links']=$table_matrix_links;

//Задаем сортировку по умолчанию
$table['sort_default']='date_buy';

//Задаем направление сортировки по умолчанию
$table['sort_direction_default']='asc';

//Меню управления
function manage_menu(){
	$manage_html="";
	$manage_html.="<div id='manage_menu'>";
	if(get_user_group($_SESSION['user'])=="writer"){
		if(@$_GET['regime']!='read'){
			$manage_html.="<h3 style='margin-top:30px;'>Действия</h3>"."<a href='".uri_make(array('add_position'=>'yes'))."'>Добавить запись</a><br/><br/>";
		}
	}
	$manage_html.="</div>";
	
	//Возвращаем результат
	return $manage_html;
}

?>
