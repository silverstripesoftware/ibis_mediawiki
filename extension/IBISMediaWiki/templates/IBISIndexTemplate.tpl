<div class="ibis_conversation">
	<strong>List of IBIS conversation (Ordered by recently added) :</strong>
	
	{if $index}
		{section name=count loop=$index}
			<p><span class="type_topic"><a href="{$base_path}/{$index[count].page}"></span>{$index[count].title}</a></p>
		{/section}
	{else}
		Sorry, No Conversations added.
	{/if}
</div>