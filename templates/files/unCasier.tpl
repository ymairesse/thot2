<div class="casier {$data.statut}" data-idtravail="{$idTravail}">
    <h4>{$data.titre}</h4>
    <div class="corps">
        <p><i class="fa fa-folder-open-o"></i>
            <a href="download.php?type=tr&amp;idTravail={$data.idTravail}&amp;fileName={$data.fileInfos.fileName}" class="fileName" data-idtravail="{$data.idTravail}" title="Télécharger">{$data.fileInfos.fileName}</a><br>
            <strong class="dateRemise" data-idTravail="{$data.idTravail}">{$data.fileInfos.dateRemise}</strong>
        </p>
        <p>

            {if isset($listeCotes.$idTravail) && ($listeCotes.$idTravail.total.cote != '')}

                <button type="button"
                        style="float:left; margin-right:0.5em;"
                        class="btn btn-xs btn-success btnVoirEval"
                        data-coursgrp="{$coursGrp}"
                        data-idTravail="{$data.idTravail}">
                    Voir
                </button>
                <strong>{$listeCotes.$idTravail.total.cote}/{$listeCotes.$idTravail.total.max}</strong>
            {else}
                {if $data.fileInfos.fileName != ''}
                <button type="button"
                        class="btn btn-xs btn-danger btnDel"
                        data-idtravail="{$data.idTravail}"
                        data-filename="{$data.fileInfos.fileName}">
                    Effacer
                </button>
                {/if}
                <span>Pas encore évalué</span>
            {/if}

        </p>
        <p>Fin: le <strong>{$data.dateFin}</strong></p>

    </div>
    <div class="bottom micro">Cliquer pour plus de détails</div>
</div>
