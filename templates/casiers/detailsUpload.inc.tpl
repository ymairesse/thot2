<style>

.fichier {
    float: left;
    padding: 0 1em;
    margin: 0.5em;
    cursor: pointer;
    word-wrap: break-word;
    }

.nomFichier {
    font-size: 9pt;
    width: 10em;
    }

.fileImage {
    background-image: url('images/file.png');
    background-repeat: no-repeat;
    background-position: center;
    height: 4em;
    width: 8em;
    }

</style>

{foreach from=$detailsTravail.fileInfos key=wtf item=unTravail}

    <div class="fichier"
        data-toggle="popover"
        title="Choisir une action"
        data-trigger="click"
        data-html="true"
        data-content="<div class='btn-group-vertical'>
                <a href='download.php?type=tr&amp;idTravail={$detailsTravail.idTravail}&amp;fileName={$unTravail.fileName}' type='button' class='btn btn-info boutonEdit' data-idtravail='{$detailsTravail.idTravail}' data-filename='{$unTravail.fileName}'>Télécharger le document</a>
                <button type='button' class='btn btn-danger btn-delFile' data-idtravail='{$detailsTravail.idTravail}' data-filename='{$unTravail.fileName}'>Supprimer le document</button>
            </div>">
        <span class="fileImage" style="display:block;"></span>

        <div class="nomFichier">
            <strong>{$unTravail.fileName}</strong><br>
            {$unTravail.size}<br>
            {$unTravail.dateRemise}
        </div>

    </div>

<script type="text/javascript">

    $(document).ready(function(){
        $('[data-toggle="popover"]').popover();

        $('body').on('click', function (e) {
		    $('[data-toggle="popover"]').each(function () {
		        if (!$(this).is(e.target) && $(this).has(e.target).length === 0 && $('.popover').has(e.target).length === 0) {
		            $(this).popover('hide');
		        }
	    	});
		});
    })

</script>

{/foreach}
