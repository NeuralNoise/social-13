<?
//������������ � ����
function db_connect()
{
    if(!mysql_connect($GLOBALS['db_host'], $GLOBALS['db_username'], $GLOBALS['db_password'])){
        trigger_error("Connection to database failed, error: ".mysql_error(), E_USER_ERROR);
        exit();
    }
    if(!mysql_select_db($GLOBALS['db_name'])){
        trigger_error("Changing database failed, error: ".mysql_error(), E_USER_ERROR);
        exit();
    }

	//������������� ��������� ��� ������ � �����
	mysql_query("SET NAMES '".$GLOBALS['db_characterset']."'");
	mysql_query("SET CHARACTER SET '".$GLOBALS['db_characterset']."'");
	mysql_query("SET SESSION collation_connection = '".$GLOBALS['db_characterset']."_general_ci'");
		
}

function db_query($question)	//Wrapper for mysql_query
{
    $debug=debug_backtrace();
	//show($debug);
    if($q=mysql_query($question))
    {
        return $q;
    }else{
        show_error("������ � ������� � ���� ������ db_query(".$question."). ������ ������ �� ����� ".$debug[0]['file']." line {$debug[0]['line']}.");
    }
}

function db_fetch($query)	//Wrapper for mysql_fetch_array
{
    return mysql_fetch_array($query);
}

function db_count($query)	//Wrapper for mysql_num_rows
{
    return mysql_num_rows($query);
}

function db_result($query='notdefined')
{
    if($query=='notdefined')
    {
        return mysql_affected_rows();
    }else{
        return mysql_affected_rows($query);
    }
}

//Easy implement a query to database and return result immediately (e.g. query + fetch = both in one)
function db_easy($question, $file='', $line='')
{
    if($a=db_query($question, $file, $line))
    {
        return db_fetch($a);
    }else{
        return false;
    }
}

function db_short_easy($question, $file='', $line='')
{
    if($a=db_query($question, $file, $line))
    {
        $result=db_fetch($a);
        return $result[0];
    }else{
        return false;
    }
}

//������� ������� ���������� ������������ ����������� ������ �� ����
function db_easy_count($question){
    return mysql_num_rows(db_query($question));
}

function db_easy_result($question)
{
    if($a=db_query($question))
    {
        return db_result($a);
    }else{
        return false;
    }
}

function db_insert_id($q="not-defined"){
	if($q=="not-defined"){
		return mysql_insert_id();
	}else{
		return mysql_insert_id($q);
	}
}
function db_disconnect($conn="none"){
	if($conn=="none"){
		mysql_close();
	}else{
		mysql_close($conn);
	}	
}
?>