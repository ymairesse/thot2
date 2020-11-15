{if $travail == Null}
    Cette note au JDC a été supprimée.
{else}
<div class="panel day-highlight dh-{$travail.class}">

    <span id="delClass"></span>
    <div class="panel-heading">
        <h3 class="panel-title cat_{$travail.idCategorie}">{$travail.categorie} <span class="pull-right">
            {if $travail.type == 'coursGrp'}
                <i class="fa fa-graduation-cap" title="Un cours"></i>
                {elseif $travail.type == 'classe'}
                <i class="fa fa-users" title="Une classe"></i>
            {/if}
        </span></h3>
    </div>

    <div class="panel-body">
        <p>Le <strong>{$travail.startDate} </strong> de <strong>{$travail.heureDebut}</strong>{if isset($travail.heureFin)} à <strong>{$travail.heureFin}</strong>{/if} Durée: <strong>{$travail.duree}' </strong></p>
        {if $travail.libelle != ''}
            <p>{$travail.libelle} {$travail.nbheures}h [{$travail.destinataire}]</p>
            {elseif $travail.type == 'classe'}
            <p>Classe {$travail.destinataire}</p>
        {/if}
        <p>Professeur <strong>{$titus}</strong> {if ($travail.redacteur!='') && ($travail.proprietaire != '')}{/if}</p>
        <h4>{$travail.title}</h4>
        <div id="unEnonce" style="border:1px solid #333; min-height:5em; margin-bottom: 1em;">{$travail.enonce}</div>

        {foreach from=$pj key=shareId item=data}
            <span class="file"><a href="download.php?type=pId&amp;fileId={$data.fileId}"><i class="fa fa-file fa-sm"></i> {$data.fileName}</a> </span>
        {/foreach}
        <p class="discret">Dernière modification: {$travail.lastModif|default:'-'}</p>

    </div>

</div>

{/if}
