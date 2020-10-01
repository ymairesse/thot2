{if $dislikes|@count > 0}
<ul class="list-unstyled">
    {foreach from=$dislikes key=matricule item=data name=dislike}
    <li title="{$data.prenom} {$data.nom}">
        {$smarty.foreach.dislike.iteration}. {$data.commentaire}
    </li>
    {/foreach}
</ul>
{else}
<i class="fa fa-smile-o fa-lg"></i> Aucun problème signalé
{/if}
