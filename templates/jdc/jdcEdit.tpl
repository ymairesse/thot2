<div id="partage"></div>

<form name="editJdc" id="editJdc" class="form-vertical">

    <div class="row">

        <div class="col-md-6 col-sm-12">

            <div class="form-group">
                <label for="categorie">Catégorie</label>
                <select name="idCategorie" id="categorie" class="form-control">
                    <option value="">Veuillez choisir une catégorie</option>
                    {foreach from=$categories key=id item=cat}
                        <option value="{$id}"{if isset($travail) && ($travail.idCategorie == $id)} selected{/if}>{$cat.categorie}</option>
                    {/foreach}
                </select>
            </div>

        </div>  <!-- col-md-... -->

        <div class="col-md-6 col-sm-12">
            {if isset($travail.id)}
            <button type="button" class="btn btn-primary btn-lg btn-block" name="button" id="btn-partager" data-id="{$travail.id}">Partager</button>
            {/if}
        </div>  <!-- col-md-... -->

    </div>  <!-- row -->

    <div class="row">

        <div class="col-md-3 col-sm-6">
            <div class="form-group">
                <label for="date" class="sr-only">Date</label>
                <input type="text" name="date" id="datepicker" value="{$travail.startDate}" placeholder="Date de notification" class="ladate form-control" autocomplete="off">
                <div class="help-block">Date de la note</div>
            </div>
        </div>

        <div class="col-md-3 col-sm-6">

            <div class="input-group">
                <label for="heure" class="sr-only">Heure</label>
                <input type="time" name="heure" id='heure' value="{$travail.heure|default:''}" class="form-control" autocomplete="off" {if isset($travail.allDay) && $travail.allDay == true}disabled{/if}>
            </div>
            <div class="help-block">Heure (modifiable)</div>

        </div>

        <div class="col-md-4 col-sm-6">

            <div class="input-group">

                <label for="listeDurees" class="sr-only">Durée</label>
                <input type="text" name="duree" id="duree" class="form-control input" value="{$travail.duree|default:''}" autocomplete="off" {if isset($travail.allDay) && $travail.allDay == true}disabled{/if}>

    			<div class="input-group-btn">
    				<button id="listeDurees" type="button" class="btn btn-primary dropdown-toggle btn-lg" data-toggle="dropdown">(min) <span class="caret" style="font-size:9pt"></span>
    				</button>
                    {assign var=heures value=range(0,8)}
    				<ul class="dropdown-menu pull-right" id="choixDuree">
                        {foreach from=$heures item=duree}
                            <li><a href="javascript:void(0)" data-value="{$duree*50}">{$duree}x50'</a></li>
                        {/foreach}
                        <li><a href="javascript:void(0)" data-value="-">Autre</a></li>
    				</ul>
                </div>    <!-- input-group-btn -->

    		</div>  <!-- input-group -->
            <div class="help-block">Durée (modifiable)</div>

        </div>  <!-- col-md-4... -->

        <div class="col-md-1 col-sm-12">

            <div class="form-group">
                <label for="journee" class="sr-only">Journée</label>
                <input type="checkbox" name="journee" id='journee' value='1'{if isset($travail.allDay) && $travail.allDay == true} checked='checked'{/if}>
                <div class="help-block">Journée entière</div>
            </div>

        </div>

        <div class="col-xs-12">
            <div class="form-group">
                <label for="titre">Titre</label>
                <input type="text" name="titre" id="titre" placeholder="Titre de la note" maxlength="100" value="{$travail.title|default:''}" class="form-control" autocomplete="off">
                <span class="help-block">Ce titre est modifiable</span>
            </div>
        </div>


    </div>  <!-- row -->

    <div class="form-group">
        <label for="enonce">Texte</label>
        <textarea name="enonce" id="enonce" class="form-control" rows="4" cols="40" maxlength="400" placeholder="Votre texte ici">{$travail.enonce|default:''}</textarea>
        <span class="help-block" id="messageLen"></span>
    </div>

    <div class="col-xs-12">
        <div class="btn-group btn-group-justified">
        <div class="btn-group">
            <button type="reset" class="btn btn-default btn-lg" style="width:50%">Annuler</button>
            <button type="button" class="btn btn-primary btn-lg" id="saveJDC" style="width:50%"><i class="fa fa-floppy-o"></i> Enregistrer</button>
        </div>
    </div>
    </div>

    <div class="clearfix"></div>

    <input type="hidden" name="id" value="{$travail.id|default:''}">

</form>

<script type="text/javascript">

function dateMysql(dateFr) {
    var date = dateFr.split('/');
    return date[2]+'-'+date[1]+'-'+date[0];
    }

jQuery.validator.addMethod (
    "dateFr",
    function(value, element) {
        return value.match(/^\d\d?\/\d\d?\/\d\d\d\d$/);
        },
    "Date au format jj/mm/AAAA svp"
    );

// -------------------------------------------------------------------------------------
// pour des raisons de compatibilité avec Google Chrome et autres navigateurs à base
// de webkit, il ne faut pas utiliser la règle "date" du validateur jquery.validate.js
// Elle sera remplacée par la règle "uneDate" dont le fonctionnement n'est pas basé sur
// le présupposé que le contenu du champ est une date. Google Chrome et Webkit traitent
// exclusivement les dates au format américain mm-dd-yyyy
// sans cette nouvelle règle, les dates du type 15-09-2012 sont refusées sous Webkit
// https://github.com/jzaefferer/jquery-validation/issues/20
// -------------------------------------------------------------------------------------
jQuery.validator.addMethod(
    "uneDate",
    function(value, element) {
        var reg=new RegExp("/", "g");
        var tableau=value.split(reg);
        // ne pas oublier le paramètre de "base" dans la syntaxe de parseInt
        // au risque que les numéros des jours et des mois commençant par "0" soient
        // considérés comme de l'octal
        // https://developer.mozilla.org/en-US/docs/JavaScript/Reference/Global_Objects/parseInt
        jour = parseInt(tableau[0],10); mois = parseInt(tableau[1],10); annee = parseInt(tableau[2], 10);
        nbJoursFev = new Date(annee,1,1).getMonth() == new Date(annee,1,29).getMonth() ? 29 : 28;
        var lgMois = new Array (31, nbJoursFev, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        condMois = ((mois >= 1) && (mois <= 12));
        if (!(condMois)) return false;
        condJour = ((jour >=1) && (jour <= lgMois[mois-1]));
        condAnnee = ((annee > 1900) && (annee < 2100));
        var testDateOK = (condMois && condJour && condAnnee);
        return this.optional(element) || testDateOK;
        },
    "Date incorrecte"
    );


    $.validator.addMethod('time', function(value, element, param) {
        return value == '' || value.match(/^([01][0-9]|2[0-3]):[0-5][0-9]$/);
    }, 'Heure invalide (hh:mm)');

$(document).ready(function(){

    $('#btn-partager').click(function(){
        var id = $(this).data('id');
        $.post('inc/jdc/modalPartage.inc.php', {
            id: id
        },
        function(resultat){
            $('#partage').html(resultat);
            $('#modePartage').modal('show');
        })
    })

    $('#enonce').on('keyup', function(){
        var maxlen = $(this).attr('maxlength');
        var length = $(this).val().length;
        if (length >= maxlen -10) {
            $('#messageLen').html('<span style="color:red">Pas plus de ' + maxlen + ' caractères</span> <span style="color:green"> [' + length + ']</span>');
        }
            else {
                $('#messageLen').html('<span style="color:green">' + length + ' caractères</span>');
            }
    })


    $('#saveJDC').click(function(){
        if ($('#editJdc').valid()) {
            var formulaire = $('#editJdc').serialize();
            $.post('inc/jdc/saveJdc.inc.php', {
                formulaire: formulaire
            }, function(resultat){
                var resultJSON = JSON.parse(resultat);
                var ERREUR = resultJSON.ERREUR;
                if (ERREUR == false) {
                    var idJdc = resultJSON.id;
                    var texte = resultJSON.texte;
                    // récupérer le contenu de la zone "travail" à droite
                    $.post('inc/jdc/getTravail.inc.php', {
                        id: 'Perso_' + idJdc
                        }, function(resultat){
                            $('#unTravail').html(resultat);
                        })
                    $('#calendar').fullCalendar('refetchEvents');
                    bootbox.alert({
                        message: texte,
                        size: 'small',
                        backdrop: true
                    });
                }
                else {
                    bootbox.alert({
                        title: 'Session expirée',
                        message: '<a class="btn btn-danger" href="index.php" role="button">Veuillez vous reconnecter <i class="fa fa-sign-out fa-lg"</i></a>',
                        backdrop: true
                    });
                }
            })
        }
    })

    $("#editJdc").validate({
        rules: {
            idCategorie: {
                required: true
                },
            destinataire: {
                required: true
                  },
            date: {
                required: true,
                uneDate: true
                },
            heure: {
                required: true,
                time: true
                },
            duree: {
                required: true
                },
            titre: {
                required: true
                },
            enonce: {
                required: true
                }
        }
    });

    $("#choixDuree li a").click(function(){
        $('#duree').val($(this).attr('data-value'))
        })

    $('#choixPeriode li a').click(function(){
        $('#heure').val($(this).attr('data-periode'));
    })

    $("#timepicker").timepicker({
    		defaultTime: 'current',
    		minuteStep: 5,
    		showSeconds: false,
    		showMeridian: false,
    		}
        );

    $('#journee').change(function(){
        var isChecked =  ($(this).is(':checked'));
        $('#heure').prop('disabled', isChecked);
        $('#duree').prop('disabled', isChecked);
    })

    $("#datepicker").datepicker({
        format: "dd/mm/yyyy",
        clearBtn: true,
        language: "fr",
        calendarWeeks: true,
        autoclose: true,
        todayHighlight: true,
        daysOfWeekDisabled: [0,6],
        }
    );

    $("#choixDuree li a").click(function(){
        $("#duree").val($(this).attr("data-value"))
        })

    $('#choixPeriode li a').click(function(){
        $('#heure').val($(this).attr('data-periode'));
    })

    $("#datepicker").change(function(){
        var date = $(this).val();
        $("#startDate").val(moment(dateMysql(date)).format('YYYY-MM-DD HH:mm'));
        })

    $('#categorie').change(function(){
        if (($('#titre').val() == '') && ($('#categorie').val() != '')) {
            var texte = $("#categorie option:selected" ).text();
            $('#titre').val(texte);
        }
    })

})

</script>
