{if isset($noBulletin)}
<div class="selecteur">

	<form name="selectBulletin" id="selectBulletin" action="index.php" method="POST" class="form-inline" role="form">
		Bulletin n° {foreach from=$listeBulletins item=bulletin} {$bulletin}
		<input type="radio" value="{$bulletin}" name="noBulletin" {if $bulletin==$noBulletin} checked{/if}> {/foreach}
		<button type="submit" class="btn btn-primary btn-xs">OK</button>
		<input type="hidden" name="action" value="{$action}">

	</form>

</div>
{/if}
