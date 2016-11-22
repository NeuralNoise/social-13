<style>
	#middle{float:left;width:30%;}
	#right{float:left;margin:0 0 0 50px;padding:0 20px 0 20px;width:30%;}
	em{font-style:italic;}
	strong{font-weight:bold;}
	#right p{margin:0;padding:0;}
	.comments p{margin:0;padding:0;}
</style>
<br/><br/>

<a href="{uri_back}">На главную</a>
<br/><br/>
<h1>Сообщения</h1>
<br/>
<h2>{title}</h2>
<span style='font-size:9pt;font-style:italic;'>Автор: {user}</span><br/> 
<span style='font-size:9pt;font-style:italic;'>Дата: {date}</span>
{edit_del_message}
{text}
<div style="margin:0;padding:0;width:100%;">
	<a href="{uri_comment}">Комментировать</a>
</div>
<div class="comments" style="margin:0;padding:0 0 0 20px;width:100%;">
	{comments}
</div>

