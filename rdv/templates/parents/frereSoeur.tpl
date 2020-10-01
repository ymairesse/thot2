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
                <form id="formAddFratrie">
                    <div class="form-group">
                        <label for="user">Nom d'utilisateur</label>
                        <input type="text" name="userName" id="userName" value="" class="form-control">
                        <span class="help-block">Votre nom d'utilisateur pour cet élève</span>
                    </div>
                    <div class="form-group">
                        <label for="passwd">Mot de passe de cet utilisateur</label>
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

        $('#fratrie').on('click', '.delFratrie', function(){
            var enfant = $(this);
            var proprio = $(this).data('userproprio');
            var userName = $(this).data('username');
            if (bootbox.confirm({
                message: 'Veuillez confirmer la suppression de ce lien',
                buttons: {
                    confirm: {
                        label: 'Supprimer',
                        className: 'btn-danger'
                    },
                    cancel: {
                        label: 'Annuler',
                        className: 'btn-default'
                        }
                    },
                callback: function (result) {
                    if (result == true) {
                        $.post('inc/parents/unlinkFrereSoeur.inc.php', {
                            proprio: proprio,
                            userName: userName
                        }, function(resultat){
                            var resultatJson = JSON.parse(resultat);
                            var nb = resultatJson.nb;
                            var message = resultatJson.messag;
                            if (resultatJson.nb == true)
                                enfant.closest('li').remove();
                            bootbox.alert(message);
                        })
                    }
                }
            }));

        })

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

        $("body").on('click', '.frereSoeur', function(){
            var newUser = $(this).data('user');
            $('.frereSoeur').removeClass('gras');
            $(this).addClass('gras');
            $.post('inc/parents/changeUser.inc.php', {
                newUser: newUser
            }, function(resultat){
                $('#nomEleve').html(resultat);
                bootbox.alert('Vous êtes maintenant sur le profil de ' + resultat);
                $.post('inc/parents/getFratrie.inc.php', {
                    }, function(resultat){
                    $("#fratrie").html(resultat);
                })
                $.post('inc/parents/getIdentite.inc.php', {
                    userName: newUser
                }, function(resultat){
                    $('#leNom').text(resultat);
                })
            })
        })

    })

</script>
