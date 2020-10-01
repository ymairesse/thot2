<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">Liste des rendez-vous pour <strong>{$User.prenomEl} {$User.nomEl} ({$User.classe})</strong></h3>
    </div>
    <div class="panel-body">
        <table class="table table-condensed" id="tableRV">
            <tr>
                <th>Heure</th>
                <th>Professeur</th>
                <th>Demandé par</th>
                <th>Local</th>
                <th>&nbsp;</th>
            </tr>
            {foreach from=$listeRV key=heure item=unRV}
            <tr data-idrv="{$unRV.idRV}">
                <td>{$unRV.heure}</td>
                {assign var=acronyme value=$unRV.acronyme}
                {assign var=sexe value=$unRV.sexe}

                <td>{if $sexe == 'F'}Mme{else}M.{/if} {$unRV.prenom} {$unRV.nom}</td>
                <td>{if $unRV.userParent != Null}{$unRV.formule} {$unRV.prenomParent} {$unRV.nomParent}{else} - {/if}</td>
                <td>{$unRV.local|default:'???'}</td>
                <td>
                    <button
                    type="button"
                    class="btn btn-danger btn-xs delRv"
                    data-idrp="{$unRV.idRP}"
                    data-idrv="{$unRV.idRV}"
                    data-nomprof="{if $sexe == 'F'}Mme{else}M.{/if} {$unRV.prenom} {$unRV.nom}"
                    data-heure="{$unRV.heure}"
                    title="Effacer"
                    ><i class="fa fa-undo"></i></button></td>
            </tr>
            {/foreach}
        </table>
    </div>
</div>
