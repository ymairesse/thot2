<div id="travauxEnCours">

    <h3>Travaux en cours</h3>
    <div style="max-height: 30em; overflow: auto">

        <ul class="list-unstyled">
            {foreach from=$listeTravauxCours key=id item=unTravail}

                <div class="input-group" style="display: block">
                    <button
                            type="button"
                            class="btn btn-default btn-block btnShowTravail{if $id == $idTravail} active{/if} {$unTravail.statut}"
                            style="overflow: hidden; text-overflow: ellipsis"
                            data-idtravail="{$id}"
                            title="{$unTravail.titre}">
                        {$unTravail.dateDebut|truncate:5:''} - {$unTravail.titre}
                    </button>
                </div>

            {/foreach}
        </ul>
    </div>

</div>


{if $listeArchives != Null}
<button type="button" class="btn btn-primary btn-block" id="btn-archives">Archives</button>
<button type="button" class="btn btn-primary btn-block hidden" id="btn-travauxEnCours">Travaux en cours</button>

<div id="travauxArchives" class="hidden">

    <h3>Travaux archivés</h3>
    <div style="max-height: 30em; overflow: auto">

        <ul class="list-unstyled">
            {foreach from=$listeArchives key=id item=unTravail}

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

</div>

{/if}
