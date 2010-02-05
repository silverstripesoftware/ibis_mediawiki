<div class="ibis_conversation">	
	<div class="ibis_parent">
		<strong>Topic(s) linking here</strong>
		<span class="ibis_parent_links">
		{if $parents}
			{section name=mysec loop=$parents}
				<a href="{$base_path}/{$parents[mysec].node}">{$parents[mysec].text}</a>
			{/section}
		{else}
			No topics
		{/if}
		</span>
	</div>
	<h2>Statement
	{if $edit_link}
		<span class="editsection">
			[<a href="{$edit_link}" >edit</a>]
		</span>
	{/if}
	</h2>
	<div class="ibis_title type_{$type}">
		{$ibis_title}
	</div>
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
	<ul>
		{if $responses}
			{section name=mysec loop=$responses}
			<li class="type_{$responses[mysec].type}">
				<a href="{$base_path}/{$responses[mysec].node}">{$responses[mysec].text}</a> 
				{if $responses[mysec].owner}
				[ <a href="{$base_path}?title={$responses[mysec].node}&action=discussion&op=edit">edit</a> ] 
				[ <a href="{$base_path}?title={$title}&action=response&op=remove&response={$responses[mysec].node}">remove</a> ] 
				{/if}
			</li>
			{/section}
		{else}
			No responses added so far.
		{/if}
	</ul>
</div>