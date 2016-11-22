<div style='padding:5px 0 20px 0;'>
	<form id='chooseVersion' method='get' action='{uri}'/>
		{hiddens}
		<div style="float:left;">
			Текущая: <select name='version' onChange="document.getElementById('chooseVersion').submit();" style=''>
				{options_current}
			</select>
			<span style='padding-left:30px'></span>
			<a href="{add}">Добавить</a>
			<span style='padding-left:30px'></span>
			<a href="{delete}" onClick="if(!confirm('Удалить?')) return false;">Удалить</a>
			<span style='padding-left:30px'></span>
			<br/>
			<div>
				Предыдущая: <select id='previous_version' name='{previous_version_name}' onChange="document.getElementById('chooseVersion').submit();" style=margin-top:5px;' {chooseprevious_disabled}>{options_previous}</select>
			</div>
			<span style='font-weight:bold;font-size:8pt;'>Режим выбора предыдущей?</span>
			<input type="checkbox" name="chooseprevious" {chooseprevious_checkbox} style="margin:5px 0 0 5pt;" onChange="if(this.checked){document.getElementById('previous_version').name='previous_version';document.getElementById('previous_version').disabled=false;}else{document.getElementById('previous_version').name='';document.getElementById('previous_version').disabled=true;}" />
		</div>
		<div style="float:left;padding-left:100px;">
			

		</div>
		<div style='clear:both;'></div>
		<input type='submit' value='Отобрать' style='display:none;'/>
	</form>
</div>
