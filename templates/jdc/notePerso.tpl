<div id="zoneDel"></div>

{if $travail == Null}
    Cette note a été supprimée.
{else}
<div class="panel day-highlight dh-{$travail.class}">

    <span id="delClass"></span>
    <div class="panel-heading">
        <h3 class="panel-title cat_{$travail.idCategorie}" style="padding: 0.5em; margin-bottom: 1em;">{$travail.title} </h3>
    </div>

    <div class="panel-body">
        <p><strong>Le {$travail.startDate} à {$travail.heure} ({$travail.duree|truncate:5:''}) </strong></p>
        <h4>{$travail.title}</h4>
        <div id="unEnonce" style="border:1px solid #333; min-height:5em; margin-bottom: 1em;">{$travail.enonce}</div>

    </div>

    <div class="panel-footer">

        <div class="btn-group btn-group-justified">
            <div class="btn-group">
                <button type="button" class="btn btn-danger btn-lg" data-id="{$travail.id}" id="delete" style="width:50%"><i class="fa fa-eraser fa-lg"></i> Supprimer</button>
                <button type="button"
                    class="btn btn-primary btn-lg"
                    data-id="{$travail.id}"
                    style="width:50%"
                    id="modifier">
                    <i class="fa fa-edit fa-lg"></i> Modifier
                </button>
            </div>
        </div>

    </div>

</div>

{/if}

<script type="text/javascript">

    $(document).ready(function(){

        // modification d'une note personnelle au JDC
        $("#unTravail").on('click', '#modifier', function() {
            var id = $(this).data('id');
            $.post('inc/jdc/getMod.inc.php', {
                id: id
                },
                function(resultat){
                    $('#unTravail').html(resultat);
                }
            )
        })

        // suppression d'une note au JDC
        $("#unTravail").on('click', '#delete', function() {
            var id = $(this).data('id');
            $.post('inc/jdc/getModalDel.inc.php', {
                    id: id,
                },
                function(resultat) {
                    $("#zoneDel").html(resultat);
                    $("#modalDel").modal('show');
                }
            )
        })


    })

</script>
