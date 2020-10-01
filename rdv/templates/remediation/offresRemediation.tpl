{if $listeOffres|@count > 0}
<div class="table-responsive">

    <table class="table table-condensed">
        <thead>
            <tr>
                <th>Date</th>
                <th>Heure</th>
                <th>Durée</th>
                <th>Matière</th>
                <th>Professeur</th>
                <th>Cible</th>
                <th>Local</th>
                <th>Occupation</th>
                <th style="width:5em;" title="Je m'inscris"><i class="fa fa-edit fa-lg pull-right"></i></th>
            </tr>
        </thead>
        {foreach from=$listeOffres key=idOffre item=data}
        <tr class="pop {$data.type}"
            data-content="<h4>{$data.title}</h4>{$data.contenu}"
            data-container="body"
            data-placement="top"
            data-html="true">
            <td>{$data.date|truncate:5:''}</td>
            <td>{$data.duree|truncate:5:''}</td>
            <td>{$data.heure}</td>
            <td>{$data.title}</td>
            <td>{if $data.sexe == 'F'}Mme{else}M.{/if} {$data.initiale}. {$data.nom}</td>
            <td>{if $data.type == 'ecole'}
                    Tous
                    {elseif $data.type == 'niveau'}
                    {$data.cible}e année
                    {elseif $data.type == 'classe'}
                    {$data.cible}
                    {elseif $data.type == 'coursGrp'}
                    {$data.cible}
                    {elseif $data.type == 'matiere'}
                    {$data.cible}
                {/if}
            </td>
            <td>{$data.local}</td>
            <td><meter value="{$occupations.$idOffre}" max="{$data.places}"></meter><span class="discret">{$occupations.$idOffre}/{$data.places}</span></td>
            <td>
                <button data-idoffre="{$idOffre}"
                        title="Je m'inscris"
                        type="button"
                        class="btn btn-success btn-xs btn-subscribe pull-right"
                        {if $occupations.$idOffre >= $data.places} disabled{/if}>
                    <i class="fa fa-edit"></i>
                </button>
            </td>
        </tr>
        {/foreach}
    </table>
</div>

<div style="border: 1px solid #aaa; padding: 0 3px; border-radius: 3px;">Légende des couleurs:
    <span class="uneCible ecole">Tous</span>
    <span class="uneCible niveau">Niveau d'étude</span>
    <span class="uneCible classe">Une classe</span>
    <span class="uneCible coursGrp">Un cours (un professeur)</span>
    <span class="uneCible matiere">Une matière (tous les professeurs)</span>
</div>

{else}
<p class="demiavertissement">Aucune offre pour l'instant</p>
{/if}

<script type="text/javascript">

    $('document').ready(function(){

        $(".pop").popover({
            trigger:'hover'
            });

    })

</script>
