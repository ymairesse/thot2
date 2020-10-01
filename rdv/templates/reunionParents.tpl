{if ($date != '') && ($ACTIVE == 1)}

<h3>Rendez-vous pour la réunion de parents {if isset($date)} du {$date}{/if}</h3>

<div class="col-xs-12">

    {$infoRp.generalites.notice}

    <p>À l'heure qu'il est, <strong>{$statistiques}</strong> rendez-vous ont été pris.</p>

</div>
{/if}
<div class="col-md-7 col-sm-12">

    {if ($ACTIVE == 1) }

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

    {include file="reunionParents/panneauListeRV.tpl"}
    {include file="reunionParents/panneauListeAttente.tpl"}

    {else}

    <div class="panel panel-info">

        <div class="panel-heading">
            <h3 class="panel-title">Prochaine réunion de parents</h3>
        </div>
        <div class="panel-body">
            <p>Le formulaire d'inscription à la prochaine réunion de parents est généralement disponible trois à quatre semaines avant la date de la réunion de parents.</p>
        </div>

    </div>
    {/if}

</div>
<!-- col-md-... -->



<div class="col-md-5 col-sm-12">

    {if ($ACTIVE == 1) && ($OUVERT == 1) }

        {if $typeRP == 'profs'}

            {include file='reunionParents/selectRVRpProfs.tpl'}

        {else}

            {include file='reunionParents/selectRVRpTitus.tpl'}

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
            var date = $("#date").val();
            var userName = $("this").data('username');
            $.post('inc/reunionParents/printRV.inc.php', {
                    date: date,
                    userName: userName,
                    module: 'thot'
                },
                function(resultat) {
                    $("#modalPrintRV").modal('show');
                })
        })

        $("#ceLien").click(function() {
            $("#modalPrintRV").modal('hide');
        })

        $("#selectRV").change(function() {
            var date = $("#date").val();
            var acronyme = $("#selectRV").val();
            $.post('inc/reunionParents/planningProf.inc.php', {
                    date: date,
                    acronyme: acronyme
                },
                function(resultat) {
                    $('#modalTableRV').html(resultat);
                    $('#modalRV').modal('show');
                }
            )
        })

        $("#selectAttente").change(function() {
            var date = $("#date").val();
            var acronyme = $("#selectAttente").val();
            $.post('inc/reunionParents/listeAttente.inc.php', {
                    date: date,
                    acronyme: acronyme
                },
                function(resultat) {
                    $('#modalTableauAttente').html(resultat);
                    $('#modalAcronyme').val(acronyme);
                    $('#modalAttente').modal('show');
                })
        })

        $(".delRv").click(function() {
            var id = $(this).data('id');
            var nomProf = $(this).data('nomprof');
            var heure = $(this).data('heure');
            $("#modalId").val(id);
            $("#modalNomProfRV").html(nomProf);
            $("#modalHeure").html(heure)
            $("#modalDelRv").modal('show');
        })

        $(".delAttente").click(function() {
            var date = $("#date").val();
            var acronyme = $(this).data('acronyme');
            var periode = $(this).data('periode');
            var heures = $(this).data('heures');
            var nomProf = $(this).data('nomprof');
            $("#modalHeures").html(heures);
            $("#modalNomProfAttente").html(nomProf);
            $("#modalAcronymeAttente").val(acronyme);
            $("#modalPeriode").val(periode);
            $("#modalDelAttente").modal('show');
        })

    })
</script>
