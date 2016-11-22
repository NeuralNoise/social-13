<form action="/versions.php?action=compare_directories" method="POST">
	<table cellspacing=0 cellpadding=0>
		<tr>
			<td><b>Директория, которую сравниваем:</b></td>
			<td><input type="text" name="dir" value='{default_dir}' style="width:300px;" /></td>
		</tr>
		<tr>
			<td><b>Дириктория, с которой сравниваем:</b></td>
			<td><input type="text" name="previous_dir" value='{default_previous_dir}' style="width:300px;" /></td>
		</tr>
		{default_direxclusions}
		<tr>
			<td colspan=2>
				<br/>
				<input type="submit" value="Сравнить">
			</td>
	</table>
</form>