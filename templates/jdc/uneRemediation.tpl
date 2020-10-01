{if $travail == Null}
    Cette remédiation a été supprimée.
{else}
<div class="panel day-highlight">

    <span id="delClass"></span>
    <div class="panel-heading">
        <h3 class="panel-title cat_{$travail.idCategorie}">{$travail.title} </h3>
    </div>

    <div class="panel-body">
        <p><strong>Le {$travail.date} à {$travail.heure} ({$travail.duree|truncate:5:''}) </strong></p>
        <h4>{$travail.title}</h4>
        <p>Professeur <strong>{$travail.nomProf}</strong> Local: <strong>{$travail.local}</strong> </p>
        <div id="unEnonce" style="border:1px solid #333; min-height:5em; margin-bottom: 1em;">{$travail.enonce}</div>

    </div>

</div>

{/if}
