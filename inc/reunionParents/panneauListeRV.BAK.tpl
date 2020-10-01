<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">Liste des rendez-vous pour {$User.prenomEl} {$User.nomEl} ({$User.classe})</h3>
    </div>
    <div class="panel-body">
        <table class="table table-condensed">
            <tr>
                <th>Heure</th>
                <th>Cours</th>
                <th>Professeur</th>
                <th>Demandé par</th>
                <th>&nbsp;</th>
            </tr>
            {foreach from=$listeRV key=heure item=unRV}
            <tr>
                <td>{$unRV.heure}</td>
                {assign var=acronyme value=$unRV.acronyme}
                {assign var=sexe value=$listeEncadrement.$acronyme.sexe}
                <td>{$listeEncadrement.$acronyme.libelle}</td>
                <td>{if $sexe == 'F'}Mme{else}M.{/if} {$listeEncadrement.$acronyme.prenom} {$listeEncadrement.$acronyme.nom}</td>
                <td>{if $unRV.userParent != Null}{$unRV.formule} {$unRV.prenomParent} {$unRV.nomParent}{else} - {/if}</td>
                <td><button
                    type="button"
                    class="btn btn-danger btn-xs delRv"
                    data-idrp="{$unRV.idRP}"
                    data-idrv="{$unRV.idRV}"
                    data-nomprof="{if $sexe == 'F'}Mme{else}M.{/if} {$listeEncadrement.$acronyme.prenom} {$listeEncadrement.$acronyme.nom}"
                    data-heure="{$unRV.heure}"
                    title="Effacer"
                    ><i class="fa fa-undo"></i></button></td>
            </tr>
            {/foreach}
        </table>
    </div>
</div>
