<div class="row">

    <div class="col-md-4 col-sm-12">

        <form name="parent" id="parent" method="POST" action="index.php" role="form" class="form-vertical" autocomplete="off">

            <div class="panel panel-info">

                <div class="panel-heading">
                    <h3 class="panel-title">Modifier votre profil</h3>
                </div>

                <div class="panel-body">

                    <p>Veuillez compléter tous les champs.</p>

                    <div class="input-group">
                        <label for="Formule"></label>
                        <span class="input-group-addon"><i class="fa fa-info-circle fa-lg fa-help"></i></span>
                        <select name="formule" id="formule" class="inputHelp form-control">
                            <option value="">Formule d'appel</option>
                            <option value="Mme" {if $identite.formule=='Mme' } selected{/if}>Madame</option>
                            <option value="M." {if $identite.formule=='M.' } selected{/if}>Monsieur</option>
                        </select>
                    </div>

                    <div class="input-group">
                        <label for="nom" class="sr-only">Nom de famille</label>
                        <span class="input-group-addon"><i class="fa fa-info-circle fa-lg fa-help"></i></span>
                        <input type="text" name="nom" id="nom" value="{$identite.nom}" maxlength="50" placeholder="Nom de famille" class="inputHelp  form-control">
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-info-circle fa-lg fa-help"></i></span>
                        <label for="prenom" class="sr-only">Prénom</label>
                        <input type="text" name="prenom" id="prenom" value="{$identite.prenom}" maxlength="50" placeholder="Prénom" class="inputHelp form-control">
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon"><i class="fa fa-info-circle fa-lg fa-help"></i></span>
                        <label for="userName" class="sr-only">Nom d'utilisateur</label>
                        <input type="text" name="userName" id="userName" value="{$identite.userName}" maxlength="25" placeholder="Nom d'utilisateur" class="inputHelp form-control" disabled>
                        <p class="help-block">Non modifiable</p>
                    </div>

                    <div class="input-group">
                        <label for="mail" class="sr-only">Adresse mail</label>
                        <span class="input-group-addon"><i class="fa fa-info-circle fa-lg fa-help"></i></span>
                        <input type="email" name="mail" id="mail" value="{$identite.mail}" maxlength="60" placeholder="Adresse mail" class="inputHelp form-control">
                    </div>

                    <div class="input-group">
                        <label for="lien" class="sr-only">Lien de parenté</label>
                        <span class="input-group-addon"><i class="fa fa-info-circle fa-lg fa-help"></i></span>
                        <input type="text" name="lien" id="lien" maxlength="20" value="{$identite.lien}" placeholder="Lien de parenté" class="inputHelp form-control">
                        <div class="input-group-btn">
                            <button aria-expanded="false" type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                                Choisir
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right" id="choixLien">
                                <li><a href="javascript:void(0)" data-value="Mère">Mère</a></li>
                                <li><a href="javascript:void(0)" data-value="Père">Père</a></li>
                                <li><a href="javascript:void(0)" data-value="Autre (merci de préciser)">Autre</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="passwd" class="sr-only"></label>
                        <span class="input-group-addon"><i class="fa fa-info-circle fa-lg fa-help"></i></span>
                        <input type="password" name="passwd" id="passwd" value="" maxlength="20" placeholder="Mot de passe souhaité" class="inputHelp form-control goodPwd">
                        <p class="help-block">Laisser vide si vous ne souhaitez pas le modifier</p>
                    </div>

                    <div class="input-group">
                        <label for="password2" class="sr-only"></label>
                        <span class="input-group-addon"><i class="fa fa-info-circle fa-lg fa-help"></i></span>
                        <input type="password" name="passwd2" id="passwd2" value="" maxlength="20" placeholder="Veuillez répéter le mot de passe" class="inputHelp form-control ">
                        <p class="help-block">Laisser vide si vous ne souhaitez pas le modifier</p>
                    </div>


                </div>
                <div class="panel-footer">
                    <div class="btn-group pull-right">
                        <button type="reset" class="btn btn-default">Annuler</button>
                        <button type="submit" class="btn btn-primary" name="submit">Enregistrer</button>
                    </div>
                    <div class="clearfix"></div>

                    <input type="hidden" name="matricule" value="{$identite.matricule}">
                    <input type="hidden" name="action" value="{$action}">
                    <input type="hidden" name="mode" value="editProfil">
                </div>
            </div>

        </form>

    </div>
    <!-- col-md-... -->


    <div class="col-md-8 col-sm-12">

        {include file="parents/profileHelp.tpl"}

    </div>
    <!-- div-md-... -->

</div>
<!-- row -->



{if isset($motifRefus) && ($motifRefus != '')}
<div id="motifRefus" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Problème</h4>
            </div>

            <div class="modal-body">
                <p>{$motifRefus}</p>
                <p>Veuillez corriger</p>
                <p class="text-danger"><i class="fa fa-warning fa-lg"></i> Les données ne sont pas enregistrées</p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fermer cette fenêtre</button>
            </div>

        </div>
        <!-- modal-content  -->
    </div>
    <!-- modal-dialog -->
</div>
<!-- motifRefus -->

<script type="text/javascript">
    $("#motifRefus").modal('show');
</script>

{/if}

<script type="text/javascript">
    function countLettres(chaine) {
        return (chaine.match(/[a-zA-Z]/g) == null) ? 0 : chaine.match(/[a-zA-Z]/g).length;
    }

    function countChiffres(chaine) {
        return (chaine.match(/[0-9]/g) == null) ? 0 : chaine.match(/[0-9]/g).length;
    }

    jQuery.validator.addMethod('goodPwd', function(value, element) {
        // validation longueur
        var condLength = (value.length >= 9);
        // validation 2 chiffres min
        var condChiffres = (countChiffres(value) >= 2)
            // validation 2 lettres min
        var condLettres = (countLettres(value) >= 2)

        var testOK = (condLength && condChiffres && condLettres);
        return this.optional(element) || testOK;
    }, "Complexité insuffisante");


    $(document).ready(function() {

        $(".help").hide();
        $(".fa-help").css('cursor', 'pointer');

        $(".inputHelp").focus(function() {
            var id = $(this).attr('id');
            $(".help").hide();
            $("#texte_" + id).fadeIn();
        })

        $(".inputHelp").blur(function() {
            $(".help").hide();
        })

        $(".fa-help").hover(function() {
            var id = $(this).closest('.input-group').find('.inputHelp').attr('id');
            $(".help").hide();
            $("#texte_" + id).fadeIn();
        })

        $("#choixLien li a").click(function() {
            $("#lien").val($(this).data('value'));
            $("#lien").select();
        })

        $("#parent").validate({
            rules: {
                formule: {
                    required: true
                },
                nom: {
                    required: true
                },
                prenom: {
                    required: true
                },
                userName: {
                    required: true
                },
                mail: {
                    required: true,
                    email: true
                },
                lien: {
                    required: true
                },
                passwd: {
                    goodPwd: true
                },
                passwd2: {
                    equalTo: "#passwd"
                }
            },
            messages: {
                lien: {
                    maxlength: 'Veuillez préciser s.v.p.'
                }
            }
        });

    })
</script>
