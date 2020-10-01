<h3>Aujourd'hui le {$smarty.now|date_format:'%d/%m '}, Joyeux Anniversaire à</h3>

{if $anniversaires|@count > 0}
<ul>
	{foreach from=$anniversaires key=matricule item=unEleve}
	<li class="anniversaire animated rollIn">{$unEleve.nomPrenom} [{$unEleve.groupe}]</li>
	{/foreach}
</ul>
{else}
<p>Pas d'anniversaire aujourd'hui ;o(</p>
{/if}
