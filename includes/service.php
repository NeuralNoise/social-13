<?
//Выводит содержание в зависимости от формата
function show($text)	
{
	$sapi=php_sapi_name();
	if(gettype($text)=='boolean')
	{
		if($text){$text='true';}else{$text='false';}
		if($sapi=='cli')
		{
			echo $text."\n";
		}else{
			echo $text."<br/>";
		}
	}elseif(gettype($text)=='array')
		if($sapi!='cli')
		{
			echo "<pre>";
			print_r($text);
			echo "</pre>";
		}else{
			print_r($text);
	}else{
		if($sapi=='cli')
		{
			echo $text."\n";
		}else{
			echo $text.'<br/>';
		}
	}
}

function show_error($text){
	show("<span style='color:red;font-size:14pt;'>".$text."</span>");
}

function h1($text)
{
	return "<h1 style='font-size:24pt;'>$text</h1>";
}

function h2($text)
{
    return "<h2 style='font-size:18pt;'>$text</h2>";
}

function get_file_name($fileName){ 
    return substr($fileName, 0, strrpos($fileName, '.'));
} 

function get_file_extension($fileName) {
    return substr($fileName, strrpos($fileName, '.') + 1);
} 

//Цепляет блок
function block($file){
	require_once($_SERVER['DOCUMENT_ROOT']."/blocks/".$file.".php");
}

//Корректирует путь в стиле UNIX
function correct_path($str_path){
	if(substr($str_path, -1)=="/"){
		return $str_path;
	}else{
		return $str_path."/";
	}
}

/*Обработчик ошибок*/
function my_error_handler($errno, $errstr, $errfile, $errline){
	show($errfile);
	show(debug_backtrace());
}
?>