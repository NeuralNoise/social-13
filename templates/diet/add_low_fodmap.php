<div id='layer1Form1' style='position:absolute;top:200px;left:300px;background:#FFFFAA;border:1px solid #666;'
		onMouseOver='add_form_over=true;' onMouseOut='add_form_out=true;' onClick="event.stopPropagation();">
		<div style="margin:0 0 0 auto;width:20px;height:15px;border-left:1px solid #666;border-bottom:1px solid #666;background-image:url(/_content/img/cross.png);"
				onClick="document.getElementById('layer1Form1').style.display='none';"></div>
	<form action='{action}' method='post' style='padding:20px;'>
		<b>Добавить низкий FODMAP продукт</b><br/>
		Наименование: <input type='text' name='name' value='' style='width:370px;'/><br/>
		<input type='submit' value='Добавить'/>
	</form>
</div>
