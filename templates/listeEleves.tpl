{if isset($listeEleves)}
<select name="userName" id="selectEleve" multiple>
	<option value="">Tous les élèves</option>
	{foreach from=$listeEleves key=user item=unEleve}
	<option value="{$user}" {if isset($userName) && ($userName==$user)} selected="selected" {/if}>{$unEleve.classe} {$unEleve.user} {$unEleve.nom} {$unEleve.prenom}</option>
	{/foreach}
</select>
{/if}
<p>{$listeEleves|@count} élèves</p>
