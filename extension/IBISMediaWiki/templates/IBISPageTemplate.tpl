<div class="ibis_conversation">	
	<div class="ibis_parent">
		<strong>Parent topic : </strong>
		<span class="ibis_parent_links">
		{if $parents}
			{section name=mysec loop=$parents}
				<span class="type_{$parents[mysec].type}"><a href="{$base_path}/{$parents[mysec].node}">{$parents[mysec].text}</a></span>
			{/section}
		{else}
			No topics
			{assign var="type" value="topic" }
		{/if}
		</span>
	</div>
	<h2>Statement
	{if $isGuestUser ne True}
	{if $edit_link}
		<span class="editsection">
			[<a href="{$edit_link}" >edit</a>]
		</span>
	{/if}
	{/if}
	</h2>
	<div class="ibis_title type_{$type}">
		{$ibis_title}  
	</div>
	<p class="ibis_meta"> Created by : {$author}, Last Edited by : {$last_author} on {$timestamp} </p>
	<p>
		{$desc}
	</p>
	<h2>Responses 
		{if $isGuestUser ne True}
		<span class="editsection">
			[<a href="{$add_response_link}" >Add a response</a>]
		</span>
		{/if}
	</h2>
	{if $responses}
		{section name=mysec loop=$responses}
		<p class="type_{$responses[mysec].type}">
			<a href="{$base_path}/{$responses[mysec].node}">{$responses[mysec].text}</a> 
			{if $responses[mysec].owner}
			[ <a href="{$base_path}?title={$responses[mysec].node}&action=discussion&op=edit">edit</a> ] 
			[ <a href="{$base_path}?title={$title}&action=response&op=remove&response={$responses[mysec].node}">remove</a> ] 
			{/if}
		</p>
		{/section}
	{else}
		No responses added so far.
	{/if}
</div>