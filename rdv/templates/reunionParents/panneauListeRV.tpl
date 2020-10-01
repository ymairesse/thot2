<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">Liste des rendez-vous pour <strong>{$User.prenomEl} {$User.nomEl} ({$User.classe})</strong></h3>
    </div>
    <div class="panel-body">
        <table class="table table-condensed">
            <tr>
                <th>Heure</th>
                {if $typeRP == 'profs'}
                <th>Cours ou fonction</th>
                {/if}
                <th>Professeur</th>
                <th>Demandé par</th>
                <th>Local</th>
                <th>&nbsp;</th>
            </tr>
            {foreach from=$listeRV key=heure item=unRV}
            <tr>
                <td>{$unRV.heure}</td>
                {assign var=acronyme value=$unRV.acronyme}
                {assign var=sexe value=$listeEncadrement.$acronyme.sexe}
                {if $typeRP == 'profs'}
                <td>{$listeEncadrement.$acronyme.libelle}</td>
                {/if}
                <td>{if $sexe == 'F'}Mme{else}M.{/if} {$listeEncadrement.$acronyme.prenom} {$listeEncadrement.$acronyme.nom}</td>
                <td>{if $unRV.userParent != Null}{$unRV.formule} {$unRV.prenomParent} {$unRV.nomParent}{else} - {/if}</td>
                <td>{$unRV.local|default:'???'}</td>
                <td>
                    {if ($ACTIVE == 1) && ($OUVERT == 1)}
                    <button
                    type="button"
                    class="btn btn-danger btn-xs delRv"
                    data-id="{$unRV.id}"
                    data-nomprof="{if $sexe == 'F'}Mme{else}M.{/if} {$listeEncadrement.$acronyme.prenom} {$listeEncadrement.$acronyme.nom}"
                    data-heure="{$unRV.heure}"
                    title="Effacer"
                    ><i class="fa fa-undo"></i></button></td>
                    {else}
                    &nbsp;
                    {/if}
            </tr>
            {/foreach}
        </table>
    </div>
</div>
