<div class="row">

<div class="alert alert-info">
    Formulaire de demande de rendez-vous pour une pré-inscription <strong>à partir de la 3e année</strong>.
</div>

    <div class="col-md-3 col-sm-6">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Veuillez choisir une date de RV</h3>
            </div>
            <div class="panel-body">

                <ul class="date list-unstyled">
                    {foreach from=$listeDatesRV item=uneDate}
                    <li>
                        <label class="radio-inline">
                            <input type="radio" name="date" class="uneDate" value="{$uneDate.date}"> <span> {$uneDate.jourSemaine} {$uneDate.datePHP}</span>
                        </label>

                    </li>

                    {/foreach}
                </ul>
            </div>
            <div class="panel-footer">

            </div>
        </div>

    </div>

    <div class="col-md-3 col-sm-6">

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Choisissez une heure de RV</h3>
            </div>
            <div class="panel-body" id="listeHeures">

                <div class="alert alert-info"> <i class="fa fa-info-circle"></i> Veuillez d'abord choisir une date dans la zone précédente </div>

            </div>
            <div class="panel-footer">
                <img src="../images/ajax-loader.gif" alt="wait" class="hidden loader">
            </div>
        </div>

    </div>
    <!-- col-md-... -->

    <div class="col-md-6 col-sm-12">

        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Informations pour la demande d'inscription</h3>
            </div>
            <div class="panel-body">
                <div id="waitDateHeure" class="alert alert-info"> <i class="fa fa-info-circle"></i> Veuillez d'abord sélectionner une date et une heure dans les zones précédentes</div>

                <form action="index.php" method="POST" role="form" class="form-vertical hidden" id="formInscription">

                    <h4>Date du RV: le <strong id="dateRV"></strong> à <strong id="heureRV"></strong></h4>


                    <div class="form-group">
                        <label for="nom" class="sr-only">Nom</label>
                        <input type="text" class="form-control" id="nom" name="nom" placeholder="Nom">
                        <p class="help-block">Le nom du/de la futur-e élève</p>
                    </div>

                    <div class="form-group">
                        <label for="prenom" class="sr-only">Prénom</label>
                        <input type="text" class="form-control" id="prenom" name="prenom" placeholder="Prénom">
                        <p class="help-block">Le prénom du/de la futur-e élève</p>
                    </div>

                    <div class="form-group">
                        <label for="mail" class="sr-only">Adresse mail</label>
                        <input type="text" class="form-control" name="email" id="email" placeholder="Adresse mail">
                        <p class="help-block">Votre adresse de courrier électronique</p>
                    </div>

                    <input type="hidden" name="id" id="id" value="">
                    <input type="hidden" name="action" value="save">

                    <div class="btn-group pull-right">
                        <button type="reset" class="btn btn-default">Annuler</button>
                        <button type="submit" class="btn btn-primary">Confirmer</button>
                    </div>

                </form>

            </div>
            <div class="panel-footer">

            </div>
        </div>

    </div>



</div>
<!-- row -->

<script type="text/javascript">
    $(document).ready(function() {

        $(document).ajaxStart(function() {
            $('body').addClass('wait');
            $('.loader').removeClass('hidden');
        }).ajaxComplete(function() {
            $('body').removeClass('wait');
            $('.loader').addClass('hidden');
        });

        $("#formInscription").validate({
            rules: {
                nom: 'required',
                prenom: 'required',
                email: {
                    required: true,
                    email: true
                }
            }

        });

        $('.uneDate').click(function() {
            $('.uneDate').closest('li').removeClass('selected');
            $(this).closest('li').addClass('selected');
            var date = $(this).val();
            $.post('inc/listeHeures.inc.php', {
                    date: date
                },
                function(resultat) {
                    $("#listeHeures").html(resultat);
                })
        })


    })
</script>
