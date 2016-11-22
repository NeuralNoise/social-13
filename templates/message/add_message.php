<div id='layer1Form1' style='position:absolute;top:200px;left:300px;background:#FFFFAA;border:1px solid #666;z-index:1;'
		onMouseOver='add_form_over=true;' onMouseOut='add_form_out=true;' onClick="event.stopPropagation();">
		<div style="margin:0 0 0 auto;width:20px;height:15px;border-left:1px solid #666;border-bottom:1px solid #666;background-image:url(/_content/img/cross.png);"
				onClick="document.getElementById('layer1Form1').style.display='none';"></div>
	<form action='{action}' method='post' style='padding:20px;'>
		<h1>{h1}</h1><br/>

		<h2>Заголовок сообщения:</h2>
		<textarea name='title' style='width:600px;height:100px;'>{title}</textarea>
		<br/><br/>
		<h2>Текст сообщения:</h2>
		<textarea name="text" style="width:100%" id="editor1">{text}</textarea><br/>
		<input type='hidden' name='uri_after_action' value='{uri_after_action}'/>
		<input type='submit' value='{button}'/>
	</form>
	
</div>
