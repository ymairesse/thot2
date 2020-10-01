<div class="panel day-highlight dh-{$travail.class}">
    <span id="delClass"></span>
    <div class="panel-heading">
        <h3 class="panel-title cat_{$travail.idCategorie}">{$travail.categorie}</h3>
    </div>

    <div class="panel-body">
        <p>Professeur
            <strong>{$travail.nom}</strong>
        </p>

        <p>
        {if $travail.libelle != ''}
            {$travail.libelle} {$travail.nbheures}h
        {/if}
        {if $travail.type == 'classe'}
            Classe de {$travail.destinataire}
        {/if}
        {if $travail.type == 'niveau'}
            Pour les élèves de {$travail.destinataire}e année
        {/if}
        {if $travail.type == 'ecole'}
            Pour tous
        {/if}
         </p>

        <p>{$travail.title}:
            <strong>Le {$travail.startDate} à {$travail.heure} ({$travail.duree}) </strong>
        </p>
        <div id="unEnonce">{$travail.enonce}</div>

    </div>

</div>
