<div class="container-fluid">
    <div class="row">
        <div class="col-md-7 col-sm-12">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    Mes prochaines remédiations
                </div>
                <div class="panel-body" id="prochainesRemediations">
                    {include file="remediation/prochainesRemediations.tpl"}
                </div>

            </div>
            <div class="panel panel-success">
                <div class="panel-heading">
                    Offres
                </div>
                <div class="panel-body" id="offresExisantes">
                    {include file="remediation/offresRemediation.tpl"}
                </div>

            </div>

        </div>
        <div class="col-md-5 col-sm-12">
            <div class="panel panel-warning">
                <div class="panel-heading">
                    Présences
                </div>
                <div class="panel-body table-responsive">
                    <table class="table table-condensed">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Heure</th>
                                <th>Matière</th>
                                <th>Professeur</th>
                                <th style="width:5em">Présence</th>
                            </tr>
                        </thead>
                        {foreach from=$listePresences key=idOffre item=data}
                        <tr class="{$data.presence}">
                            <td>{$data.date|truncate:5:''}</td>
                            <td>{$data.heure}</td>
                            <td>{$data.title}</td>
                            <td>{if $data.sexe == 'M'}M. {else}Mme {/if} {$data.initiale}. {$data.nom}</td>
                            <td style="text-align:center">{if $data.presence == 'absent'}<i class="fa fa-thumbs-down fa-lg"></i>{elseif $data.presence == 'present'}<i class="fa fa-thumbs-up fa-lg"></i> {else}<i class="fa fa-question"></i> {/if}</td>
                        </tr>
                        {/foreach}
                    </table>
                </div>
            </div>

        </div>

    </div>

</div>

<div id="modalSubscribe" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalSubscribeLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
        <h4 class="modal-title" id="modalSubscribeLabel">Je confirme mon inscription</h4>
      </div>
      <div class="modal-body">
        ...
      </div>
    </div>
  </div>
</div>

<script type="text/javascript">

    $(document).ready(function(){

        $('#offresExisantes').on('click', '.btn-subscribe', function(){
            var idOffre = $(this).data('idoffre');
            $.post('inc/remediation/confirmSubscribe.inc.php', {
                idOffre: idOffre
            }, function(resultat){
                $('#modalSubscribe .modal-body').html(resultat);
                $('#modalSubscribe').modal('show');
            })
        })

        $('#modalSubscribe').on('click', '#btnConfirmSubscribe', function(){
            var idOffre = $(this).data('idoffre');
            $.post('inc/remediation/subscribe.inc.php', {
                idOffre: idOffre
            }, function(resultat){
                $('#modalSubscribe').modal('hide');
                $('#prochainesRemediations').html(resultat);
                $.post('inc/remediation/renewOffre.inc.php', {},
                    function(resultat){
                        $('#offresExisantes').html(resultat);
                    })

            })
        })
    })

</script>
