<div class="row">

    <div class="col-md-4 col-sm-12">
        <div class="panel panel-default">

            <div class="panel-heading">
                Ma classe {$classe}
            </div>
            <div class="panel-body" style="max-height: 30em; overflow: auto;">
                <ul class="list-unstyled">
                {foreach from=$listeElevesClasse key=matricule item=dataEleve}
                <li><a href="javascript:void(0)" class="mail" data-mail="{$dataEleve.user}@{$dataEleve.mailDomain}">{$dataEleve.nom} {$dataEleve.prenom}</a></li>
                {/foreach}
                </ul>
            </div>
          <div class="panel-footer">{$listeElevesClasse|@count} adresses</div>
        </div>

    </div>

    <div class="col-md-4 col-sm-12">
        <div class="panel panel-default">
            <div class="panel-heading">
                Mes cours
            </div>
            <div class="panel-body">
                <select class="form-control" name="listeCours" id="listeCours">
                    <option value="">Sélectionner un cours</option>
                    {foreach from=$listeCours key=coursGrp item=dataCours}
                        <option value="{$coursGrp}">{$dataCours.libelle} {$dataCours.nbheures}h</option>
                    {/foreach}
                </select>
                <div id="elevesCours" style="max-height:25em; overflow:auto;">

                </div>
                <div id="footerElevesCours" class="panel-footer">

                </div>
            </div>
        </div>

    </div>

    <div class="col-md-4 col-sm-12">
        <div class="notice">
        <h4>Quelques règles</h4>
        <p>Les adresses mail scolaires sont <strong>des informations privées</strong> qui ne doivent pas être diffusées hors du cadre de l'école.</p>
        <p>Les adresses mail <strong>scolaires</strong> devraient être réservées à l'usage <strong>scolaire</strong>.</p>
        <p>Si j'utilise mon adresse mail scolaire pour communiquer avec une personne étrangère à l'école (lieu de stage,...), je sais que je suis responsable de l'image de l'école que je donne.</p>
        <p style="font-weight: bold;"><i class="fa fa-warning fa-2x"></i> Lorsque j'envoie un mail, je m'assure toujours qu'il ne contient aucun élément qui <u>pourrait</u> choquer (phrase, expression, image) mon correspondant.</p>
        </div>

        <div class="notice">
            <p>Adresses des interfaces Webmail:</p>
            <ul>
                <li><a href="http://mail.isnd.be" target="_blank"><img src="images/roundcube.png" alt="Roundcube"></a></li>
                <li><a href="https://isnd.be/mail" target="_blank"><img src="images/rainloop.png" alt="Rainloop"></a></li>
            </ul>
        </div>
    </div>

</div>


<script type="text/javascript">

    $(document).ready(function(){

        $('body').on('click', '.mail', function(){
            var mail = $(this).data('mail');
            var nomEleve = $(this).text();
            bootbox.alert({
                size: 'small',
                title: 'Adresse mail',
                message: 'L\'adresse mail de '+ nomEleve + ' est <br><a href="mailto:'+ mail + '">' + mail + '</a>',
                backdrop: false
            })
        })

        $('#listeCours').on('change', function(){
            var coursGrp = $('#listeCours').val();
            $.post('inc/gestMails/listeAdressesCours.inc.php', {
                coursGrp: coursGrp
                },
                function(resultat){
                    $('#elevesCours').html(resultat);
                })
            $.post('inc/gestMails/nbAdressesCours.inc.php', {
                coursGrp: coursGrp
                },
                function(resultat){
                    $('#footerElevesCours').text(resultat);
                })
        })
    })

</script>
