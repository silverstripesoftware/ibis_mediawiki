<form method="post" action="">
	<table>
	<tr>
		<td>
			Type:
		</td>
		<td>
			<select name="type">
				<option value="issue" { $issue }>Issue</option>
				<option value="position" { $position } >Position</option>
				<option value="supporting_argument" { $supporting_argument } >Supporting Argument</option>
				<option value="opposing_argument" { $opposing_argument } >Opposing Argument</option>
			</select>
		</td>
	</tr>
	<tr>
		<td>
			Title:
		</td>
		<td>
			<input type="text" name="ibis_title" class="ibis_title" value="{$title}"/>
		</td>
	</tr>
	<tr>
		<td>
			Description:
		</td>
		<td>
			<textarea rows="3" cols="25" name="desc" >{$desc}</textarea>
		</td>
	</tr>
	<tr>
		<td colspan="2">
			<input type="hidden" name="user" value="{$user}" />
			<input type="submit" value="Save" name="save">
			<input type="submit" value="Cancel" name="cancel"/>
		</td>
	</tr>
	</table>
</form>
