{if ($idRP != '') && ($ACTIVE == 1)}

<h3>Rendez-vous pour la réunion de parents {if isset($date)} du {$date}{/if}</h3>

<div class="col-xs-12">

    {$infoRP.generalites.notice}

    <p>À l'heure qu'il est, <strong>{$statistiques}</strong> rendez-vous ont été pris.</p>

</div>
{/if}
<div class="col-md-7 col-sm-12">

    {if (isset($ACTIVE) && ($ACTIVE == 1)) }

    <div class="row">

        <div class="col-md-4 col-sm-12">
            <button type="button" class="btn btn-success btn-block" id="print" data-username={$userName}>
                Imprimer en PDF <i class="fa fa-print"></i>
            </button>
        </div>
        <div class="col-md-8 col-sm-12">
            <div id="ajaxLoader" class="hidden">
                <img src="images/ajax-loader.gif" alt="loading" class="center-block">
            </div>
        </div>

    </div>

    <div id="panelRV">
        {include file="reunionParents/panneauListeRV.tpl"}
    </div>

    <div id="panelAttente">
            {include file="reunionParents/panneauListeAttente.tpl"}
    </div>

    {else}

    <div class="panel panel-info">

        <div class="panel-heading">
            <h3 class="panel-title">Prochaine réunion de parents</h3>
        </div>
        <div class="panel-body">
            <p>Veuillez sélectionner une date ci-dessus</p>
        </div>

    </div>
    {/if}

</div>
<!-- col-md-... -->





<div class="col-md-5 col-sm-12">

    {if isset($ACTIVE) && ($ACTIVE == 1) && ($OUVERT == 1) }

        {if $typeRP == 'profs'}
            {include file='reunionParents/selectRVRpProfs.tpl'}
        {elseif $typeRP == 'titus'}
            {include file='reunionParents/selectRVRpTitus.tpl'}
        {else}
            {include file='reunionParents/selectRVRpCibles.tpl'}
        {/if}

    {else}

    <div class="panel panel-default">

        <div class="panel-heading">
            <h3 class="panel-title">Inscriptions à la réunion de parents</h3>
        </div>
        <div class="panel-body">
            <p>L'inscription n'est pas encore ouverte ou n'est plus ouverte</p>
        </div>

    </div>

    {/if}
</div>










{include file="reunionParents/modalRV.tpl"}
{include file="reunionParents/modalAttente.tpl"}
{include file="reunionParents/modalDelRv.tpl"}
{include file="reunionParents/modalDelAttente.tpl"}
{include file="reunionParents/modalPrintRV.tpl"}

<script type="text/javascript">
    $(document).ready(function() {

        $(document).ajaxStart(function() {
            $('#ajaxLoader').removeClass('hidden');
        }).ajaxComplete(function() {
            $('#ajaxLoader').addClass('hidden');
        });

        $("#print").click(function() {
            var idRP = $("#idRP").val();
            $.post('inc/reunionParents/printRV.inc.php', {
                    idRP: idRP
                },
                function(resultat) {
                    $("#modalPrintRV").modal('show');
                })
        })

        $("#ceLien").click(function() {
            $("#modalPrintRV").modal('hide');
        })


        $("#selectRV").change(function() {
            var idRP = $("#idRP").val();
            var acronyme = $("#selectRV").val();
            $.post('inc/reunionParents/planningProf.inc.php', {
                    idRP: idRP,
                    acronyme: acronyme
                },
                function(resultat) {
                    $('#modalTableRV').html(resultat);
                    $('#modalRV').modal('show');
                }
            )
        })

        $('#modalRV').on('click', '#confSetRV', function(){
            var idRP = $(this).data('idrp');
            var idRV = $('.radioRv:checked').val();
            if (idRV != undefined) {
                $.post('inc/reunionParents/newRV.inc.php', {
                    idRP: idRP,
                    idRV: idRV
                }, function(resultat){
                    var resultatJSON = JSON.parse(resultat);
                    var OK = resultatJSON.ok;
                    var message = resultatJSON.message;
                    $.post('inc/reunionParents/putListeRV.inc.php', {
                        idRP: idRP
                    }, function(resultat){
                        $('#panelRV').html(resultat);
                    })
                    $("#modalRV").modal('hide');
                    bootbox.alert({
                        title: 'Fixation d\'un RV',
                        message: message,
                    });
                })
                }
                else bootbox.alert('Veuillez choisir une période de RV');
            })

        $("#selectAttente").change(function() {
            var idRP = $("#idRP").val();
            var acronyme = $("#selectAttente").val();
            $('#confSetAttente').data('idrp', idRP);
            $('#confSetAttente').data('acronyme', acronyme);
            $.post('inc/reunionParents/listeAttente.inc.php', {
                    idRP: idRP,
                    acronyme: acronyme
                },
                function(resultat) {
                    $('#modalTableauAttente').html(resultat);
                    $('#modalAcronyme').val(acronyme);
                    $('#modalAttente').modal('show');
                })
        })

        $('#modalAttente').on('click', '#confSetAttente', function(){
            var idRP = $("#confSetAttente").data('idrp');
            var acronyme = $("#confSetAttente").data('acronyme');
            var periode = $('.periode:checked').val();
            $.post('inc/reunionParents/newAttente.inc.php', {
                idRP: idRP,
                acronyme: acronyme,
                periode: periode
            }, function(resultat){
                var resultatJSON = JSON.parse(resultat);
                var OK = resultatJSON.ok;
                var message = resultatJSON.message;
                if (OK == true) {
                    $('#modalAttente').modal('hide');
                    $.post('inc/reunionParents/putListeAttente.inc.php', {
                        idRP: idRP
                    }, function(resultat){
                        $('#panelAttente').html(resultat);
                    })
                }
                bootbox.alert({
                    title: 'RV en liste d\'attente',
                    message: message
                })
            })
        })

        $('#panelRV').on('click', ".delRv", function() {
            var idRP = $(this).data('idrp');
            var idRV = $(this).data('idrv');
            var nomProf = $(this).data('nomprof');
            var heure = $(this).data('heure');
            $('#confDelRV').data('idrp', idRP);
            $('#confDelRV').data('idrv', idRV);
            $("#modalIdRP").val(idRP);
            $("#modalIdRV").val(idRV);
            $("#modalNomProfRV").html(nomProf);
            $("#modalHeure").html(heure)
            $("#modalDelRv").modal('show');
        })

        $('#modalDelRv').on('click', '#confDelRV', function(){
            var idRP = $(this).data('idrp');
            var idRV = $(this).data('idrv');
            $.post('inc/reunionParents/delRV.inc.php', {
                idRP: idRP,
                idRV: idRV
            }, function(resultat){
                var resultatJSON = JSON.parse(resultat);
                var OK = resultatJSON.ok;
                var message = resultatJSON.message;
                $("#modalDelRv").modal('hide');
                bootbox.alert({
                    title: 'Suppression d\'un RV',
                    message: message
                });
                if (OK == true)
                    $('#tableRV tr[data-idrv="' + idRV + '"]').remove();
            })
        })

        $("#panelAttente").on('click', ".delAttente", function() {
            var idRP = $('#idRP').val();
            var acronyme = $(this).data('acronyme');
            var periode = $(this).data('periode');
            var heures = $(this).data('heures');
            var nomProf = $(this).data('nomprof');
            $('#confDelAttente').data('idrp', idRP);
            $('#confDelAttente').data('periode', periode);
            $('#confDelAttente').data('acronyme', acronyme);
            $("#modalHeures").html(heures);
            $("#modalNomProfAttente").html(nomProf);
            $("#modalAcronymeAttente").val(acronyme);
            $("#modalPeriode").val(periode);
            $("#modalDelAttente").modal('show');
        })

        $('#modalDelAttente').on('click', '#confDelAttente', function(){
            var idRP = $('#idRP').val();
            var acronyme = $(this).data('acronyme');
            var periode = $(this).data('periode');
            $.post('inc/reunionParents/delAttente.inc.php', {
                idRP: idRP,
                acronyme: acronyme,
                periode: periode
            }, function(resultat){
                var resultatJSON = JSON.parse(resultat);
                var OK = resultatJSON.ok;
                var message = resultatJSON.message;
                if (ok = true) {
                    $('#modalDelAttente').modal('hide');
                    $('#panelAttente table tr[data-periode="' + periode + '"][data-acronyme="' + acronyme + '"]').remove();
                    bootbox.alert({
                        title: 'Suppression de la liste d\'attente',
                        message: message
                    })
                }
            })
        })


    })
</script>
