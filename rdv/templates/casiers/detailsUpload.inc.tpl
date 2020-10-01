<p>Nom du fichier: <span id="fileName">
    {if trim($detailsTravail.fileInfos.fileName) == ''}
        {if $detailsTravail.statut == termine}
            <strong>Non remis </strong>
            {else}
            <strong>En attente </strong>
        {/if}
        {else}

        <a href="download.php?type=tr&amp;idTravail={$detailsTravail.idTravail}&amp;fileName={$detailsTravail.fileInfos.fileName}"
        class="fileName"
        title="Télécharger">
            {$detailsTravail.fileInfos.fileName}
        </a>
        <br>
        Date de remise: <strong id="dateRemise">{$detailsTravail.fileInfos.dateRemise}</strong>

    {/if}
    </span>
    {if ($detailsTravail.fileInfos.fileName != '') && ($totalTravail.cote == Null)}
        <button title="Supprimer le fichier" type="button" class="btn btn-default btn-sm" id="btn-delFile"><i class="fa fa-times text-danger"></i></button>
    {/if}
</p>
