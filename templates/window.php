<div id='window{id}' class='windows' style='position:absolute;top:150px;left:350px;'>
	<div class='handle' onmousedown="dragOBJ(this,event); return false;">
		<a id='window_close{id}' class='windows_close'></a>
	</div>
	<form action='{action}' method='post' class='windows_form'>
		{html}
	</form>
	<div id='temp'></div>
</div>
