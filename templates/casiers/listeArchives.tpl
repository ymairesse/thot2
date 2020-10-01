{if $listeTravauxCours == Null}
    <p>Rien actuellement</p>

{else}
    <h3>Archives</h3>
    <div style="max-height: 30em; overflow: auto">

        <ul class="list-unstyled">
            {foreach from=$listeTravauxCours key=id item=unTravail}

                <div class="input-group">
                    <button
                            type="button"
                            class="btn btn-default btn-block btnShowTravail{if $id == $idTravail} active{/if} {$unTravail.statut}"
                            style="width: 18em; overflow: hidden; text-overflow: ellipsis"
                            data-idtravail="{$id}"
                            title="{$unTravail.titre}">

                        {$unTravail.dateDebut|truncate:5:''} - {$unTravail.titre}
                    </button>
                </div>

            {/foreach}
        </ul>
    </div>

{/if}

<button type="button" class="btn btn-primary btn-block" id="btn-travaux">Travaux en cours</button>
