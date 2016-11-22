<?
function menu_top($manual_items=""){
	db_connect();
	$html_items="	
					<a href='/versions.php?action=show_version' style='font-size:9pt;'>Контроль версий</a><span class='divider'></span>
					<a href='".uri_make(array('UriScript'=>'mail_stat.php', 'show_log'=>'yes'))."' style='font-size:9pt;'>Статистика Postfix</a><span class='divider'></span>
					<a href='/books.php' style='font-size:9pt;'>Книги</a>
					<a href='#' style='font-size:9pt;'>Grab</a><span class='divider'></span>
					<a href='/diet.php' style='font-size:9pt;'>FODMAP диета</a><span class='divider'></span>
					<a href='/robot.php' style='font-size:9pt;'>Робот</a><span class='divider'></span>
					<a href='/intranet.php' style='font-size:9pt;'>Интранет</a><span class='divider'></span>
					<br/>
					";
					
    //Запрашиваем из таблицы БД 'menus' все пункты меню, принадлежащие размещению 'top'
    $items_query=db_query("SELECT `href`, `text`, `label` FROM `menu_items` WHERE `area`='top' ORDER BY `order` ASC");

    $number_item=1;
	
	//Ручное добавление пунктов
	$html_items.=$manual_items;
	
    //Пункты меню найдены
    if(db_count($items_query)>0){
        //Перебираем пункты меню
        while($item=db_fetch($items_query)){
            $html_items.="<a href='{$item['href']}' class='".get_class_depend_on_uri("!=", 'table_name', $item['label'])."'>{$item['text']}</a>";
            $html_items.="<span class='divider'></span>";
            if($number_item % 7 == 0) $html_items.="<br/>";
            $number_item++;
        }
    //Такие пункты меню не найдены
    }
    return template_get('menus/menu_top', array('html_items'=>$html_items, 'login'=>$_SESSION['user']));
}?>