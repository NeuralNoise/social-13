<?php
//
function get_window($action, $html, $id=''){
	return template_get('window', array('id'=>$id, 'action'=>$action, 'html'=>$html));	
}
?>