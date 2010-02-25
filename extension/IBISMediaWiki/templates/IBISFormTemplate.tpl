<form method="post" action="">
	<table style="width:100%">
	{if $isNew}
	<input type="hidden" value="topic" name="type" />
	{else}
	<tr>
		<td >
			Type:
		</td>
		<td>
			<select name="type" class="ibis_type">
				<option class="issue" value="issue" { $issue }>Issue</option>
				<option class="position" value="position" { $position } >Position</option>
				<option class="supporting_argument" value="supporting_argument" { $supporting_argument } >Support</option>
				<option class="opposing_argument" value="opposing_argument" { $opposing_argument } >Oppose</option>
			</select>
		</td>
	</tr>
	{/if}
	<tr>
		<td style="width:60px;">
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
			<!--<textarea id="desc" rows="3" cols="25" name="desc" >{$desc}</textarea>-->
			{php}
			$editor_path = "extensions/IBISMediaWiki/includes/fckeditor/"; 
			$FCKeditor = new FCKeditor('desc');
			$FCKeditor->BasePath = $editor_path;
			print_r($FCKEditor);
			$desc = $this->get_template_vars('desc');
			$FCKeditor->Height = '350px';
			$FCKeditor->ToolbarSet = 'Basic';
			$FCKeditor->Value = $desc;
			$FCKeditor->Create();
			{/php}
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
