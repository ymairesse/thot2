<div id="modalDel" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalDelLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" id="modalDelLabel">Confirmation de l'effacement</h4>
      </div>
      <div class="modal-body">
          <form class="form-vertical">

              <p class="cat_{$travail.idCategorie}"><strong>{$travail.title}</strong></p>
              <div style="border:1px solid #333; min-height:5em; margin-bottom: 1em;">{$travail.enonce}</div>
              <p>Date: <strong>{$travail.startDate}</strong></p>
              {if $travail.allDay == 1}
                  <p><strong>Toute la journée</strong></p>
                  {else}
                  <p>Heure: <strong>{$travail.heure}</strong> Durée: <strong>{$travail.duree}</strong> </p>
              {/if}

              <button type="button" class="btn btn-danger pull-right" id="btn-modalDel" data-id="{$travail.id}"><i class="fa fa-eraser fa-lg"></i> Supprimer</button>
          </form>

          <div class="clearfix"></div>
      </div>
      <div class="modal-footer">
        ...
      </div>
    </div>
  </div>
</div>



<script type="text/javascript">

    $(document).ready(function(){

        $('#btn-modalDel').click(function(){
            var id = $(this).data('id');
            $.post('inc/jdc/delJdc.inc.php', {
                id: id
            }, function(resultat){
                if (resultat == 1) {
                    bootbox.alert({
                        message: "Événement supprimé",
                        size: 'small',
                        backdrop: true
                    });
                    $('#unTravail').html('');
                    $('#unTravail').load('templates/jdc/selectItem.html');
                }
                $('#calendar').fullCalendar('refetchEvents');
                $('#modalDel').modal('hide');
            })
        })

    })

</script>
