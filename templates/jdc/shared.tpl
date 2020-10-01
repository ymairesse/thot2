{if $travail == Null}
    Cette note a été supprimée.
{else}
<div class="panel day-highlight jdcShared">

    <span id="delClass"></span>
    <div class="panel-heading">
        <h3 class="panel-title cat_{$travail.idCategorie}" style="padding: 0.5em; margin-bottom: 1em;">{$travail.title} </h3>
    </div>

    <div class="panel-body">
        <p><strong>Le {$travail.startDate} à {$travail.heure} ({$travail.duree|truncate:5:''}) </strong></p>
        <h4>{$travail.title}</h4>
        <div id="unEnonce" style="border:1px solid #333; min-height:5em; margin-bottom: 1em;">{$travail.enonce}</div>

    </div>

    <div class="panel-footer">

        Partagé par {$nom}

    </div>

</div>

{/if}
