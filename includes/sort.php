<?
//Получаем колонку, по которой сортируем из адресной строки
function get_sort(){
    if(isset($_GET['sort'])){
        if(preg_match("/^[A-z0-9_]{1,50}$/", $_GET['sort'])){
            return $_GET['sort'];
        }else{
            return false;
        }
    }else{
        return false;
    }
}

//Получаем направление сортировки из адресной строки
function get_sort_direction(){
    if(isset($_GET['sort_direction'])){
        if(preg_match("/^[A-z0-9_]{1,20}$/", $_GET['sort_direction'])){
            return $_GET['sort_direction'];
        }else{
            return false;
        }
    }else{
        return false;
    }
}

//Инициализируем сортировку
function init_sort(&$table){
	if(!$GLOBALS['sort']=get_sort()) $GLOBALS['sort']=$table['sort_default'];
	if(!$GLOBALS['sort_direction']=get_sort_direction()) $GLOBALS['sort_direction']=$table['sort_direction_default'];
	$table['sort']=$sort;
	$table['sort_direction']=$sort_direction;
}
?>