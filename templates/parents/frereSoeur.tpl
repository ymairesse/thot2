<div class="row">

    <div class="col-xs-12 col-md-4" id="fratrie">

        {include file="parents/fratrie.tpl"}

    </div>

    <div class="col-xs-12 col-md-4">
        <div class="panel panel-success">
            <div class="panel-heading">
                <h3>Ajouter un membre de la famille</h3>
            </div>
            <div class="panel-body">
                <p>Dans ce cadre, vous allez pouvoir ajouter des membres de la famille.<br>
                    Pour cela, indiquez votre <strong>identifiant de "parent"</strong> pour chaque autre enfant et <strong>votre mot de passe</strong> correspondant à cet identifiant "parent".</p>
                <form id="formAddFratrie">
                    <div class="form-group">
                        <label for="user">Nom d'utilisateur "parent" pour un autre enfant (vous devez avoir été invité-e)</label>
                        <input type="text" name="userName" id="userName" value="" class="form-control">
                        <span class="help-block">Votre nom d'utilisateur pour cet élève</span>
                    </div>
                    <div class="form-group">
                        <label for="passwd">Mot de passe de cet utilisateur "parent"</label>
                        <input type="password" name="passwd" id="passwd" value="" class="form-control">
                        <span class="help-block">Votre mot de passe pour cet élève</span>
                    </div>

                    <div class="btn-group pull-right">
                        <button type="reset" class="btn btn-default" name="reset">Annuler</button>
                        <button type="button" class="btn btn-primary" id="ajoutFratrie" name="ajoutFratrie">Ajouter</button>
                    </div>
                </form>

            </div>
        </div>
    </div>


    <div class="col-xs-12 col-md-4">
        <div class="panel panel-info">
            <div class="panel-heading">
                <h3><i class="fa fa-info-circle fa-2x"></i> Aide</h3>
            </div>
            <div class="panel-body">
                <p>Comment ne plus avoir qu'un seul identifiant pour plusieurs enfants.</p>
                <p>Dans la zone "Ajouter un membre de la famille", indiquez:
                    <ul>
                        <li>votre identifiant (y compris les quatre chiffres) pour un frère ou une sœur</li>
                        <li>le mot de passe pour cet identifiant</li>
                    </ul>
                </p>
                <p>La liste des frères et sœurs se complète dans le cadre de gauche.</p>
                <p>Pour consulter les informations d'un frère ou d'une sœur, cliquez sur son nom dans ce premier cadre. Plus aucun mot de passe ne sera demandé.</p>
                <p>Il faut réaliser la même manœuvre pour chaque membre de la famille.</p>

            </div>

        </div>
    </div>

</div>


<script type="text/javascript">

    $(document).ready(function(){

        $('#formAddFratrie').validate({
            rules: {
                userName: {
                    required: true,
                },
                passwd: {
                    required: true,
                }
            }
        })

        // $('#fratrie').on('click', '.delFratrie', function(){
        //     var enfant = $(this);
        //     var proprio = $(this).data('proprio');
        //     var userParent = $(this).data('userparent');
        //     if (bootbox.confirm({
        //         message: 'Veuillez confirmer la suppression de ce lien',
        //         buttons: {
        //             confirm: {
        //                 label: 'Supprimer',
        //                 className: 'btn-danger'
        //             },
        //             cancel: {
        //                 label: 'Annuler',
        //                 className: 'btn-default'
        //                 }
        //             },
        //         callback: function (result) {
        //             if (result == true) {
        //                 $.post('inc/parents/unlinkFrereSoeur.inc.php', {
        //                     // proprio: proprio,
        //                     userParent: userParent
        //                 }, function(resultat){
        //                     var resultatJson = JSON.parse(resultat);
        //                     var nb = resultatJson.nb;
        //                     var message = resultatJson.messag;
        //                     if (resultatJson.nb == true)
        //                         enfant.closest('li').remove();
        //                     bootbox.alert(message);
        //                 })
        //             }
        //         }
        //     }));
        // })

        $('#ajoutFratrie').click(function(){
            if ($('#formAddFratrie').valid()) {
                var userName = $('#userName').val();
                var passwd = $('#passwd').val();
                $.post('inc/parents/addFratrie.inc.php', {
                    userName: userName,
                    passwd: passwd
                }, function(resultat){
                    bootbox.alert(resultat);
                    $.post('inc/parents/getFratrie.inc.php', {
                        }, function(resultat){
                        $("#fratrie").html(resultat);
                    })
                })
            }

        })

        $("#fratrie").on('click', '.frereSoeur', function(){
            var newUser = $(this).data('user');
            $('.frereSoeur').removeClass('gras');
            $(this).addClass('gras');
            $.post('inc/parents/changeUser.inc.php', {
                newUser: newUser
            }, function(resultat){
                $('#nomEleve').html(resultat);
                bootbox.alert('Vous êtes maintenant sur le profil de <strong>' + resultat + '</strong>');
                $.post('inc/parents/getFratrie.inc.php', {
                    }, function(resultat){
                    $("#fratrie").html(resultat);
                    $.post('inc/parents/getIdentite.inc.php', {
                        userName: newUser
                    }, function(resultat){
                        $('#leNom').text(resultat);
                        $('#userNameLog').text(newUser);
                    })

                })

            })
        })

    })

</script>
