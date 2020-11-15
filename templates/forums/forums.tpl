<div class="row">

    <div class="col-md-3 col-xs-12" style="min-height:15em; overflow: auto;" id="listeSujets">
        {* Pour chaque catégorie *}
        {foreach from=$listeTypes key=type item=nomDuType}
            {if isset($listeSujets.$type)}
                <h3>Diffusé à {$nomDuType}</h3>
                {foreach from=$listeSujets.$type key=groupeDiffusion item=listeData}
                    <h4>Groupe {$groupeDiffusion}</h4>

                    {foreach from=$listeData key=idCategorie item=dataSujets}

                        {foreach from=$dataSujets key=idSujet item=leSujet}

                            <button type="button"
                                class="btn btn-primary btn-block btn-{$type} btn-sujet pop"
                                data-idcategorie="{$leSujet.idCategorie}"
                                data-idsujet="{$leSujet.idSujet}"
                                data-content="{$leSujet.sujet}"
                                data-container="body"
                                data-placement="top"
                                data-sujet="{$leSujet.sujet}"
                                data-title="{$leSujet.libelle}"
                                data-type="{$type}">
                                <strong>[{$leSujet.libelle}]<br></strong> {$leSujet.sujet}<br>
                            {$leSujet.nomProf} Le {$leSujet.ladate} à {$leSujet.heure}
                            </button>

                        {/foreach}

                    {/foreach}

                {/foreach}
            {/if}

        {/foreach}

    </div>

    <div class="col-md-9 col-xs-12">

        <div class="panel panel-info">
            <div class="panel-heading" id="libelle" data-defaulttext="Liste des contributions">
                <span id="titreSujet">Liste des contributions</span>
                <div class="btn-group pull-right">
                    <button type="button" class="btn btn-success btn-xs" id="btn-hideRepondre" title="Cacher les boutons"><i class="fa fa-eye-slash"></i> </button>
                    <button type="button" class="btn btn-warning btn-xs" id="btn-allText" title="Texte continu"><i class="fa fa-text-height"></i></button>
                    <button type="button" class="btn btn-info btn-xs" id="btn-date" disabled><i class="fa fa-calendar"></i> <span id="laDate">Date</span></button>
                </div>
            </div>
            <div class="clearfix"></div>

            <div id="listePosts" style="min-height:35em; overflow: auto;">

            </div>
        </div>

    </div>

</div>

<div id="modal">
</div>


<script type="text/javascript">

    $(document).ready(function(){

        var dateForum = Cookies.get('dateForum');
        $('#btn-date #laDate').text(dateForum);

        $('#btn-date').click(function(){
            var laDate = $(this).text();
            $.post('inc/forums/modalChoixDate.inc.php', {
                laDate: laDate
            }, function(resultat){
                $('#modal').html(resultat);
                $('#modalDate').modal('show');
            })
        })

        $('#btn-allText').click(function(){
            $('.postForum').toggleClass('em8');
            if ((Cookies.get('texteSuivi') == undefined) || (Cookies.get('texteSuivi') == "false"))
                Cookies.set('texteSuivi', "true", { expires : 7 })
                else Cookies.set('texteSuivi', "false", { expires : 7 })
        })

        $('#modal').on('click', '#btn-confirmDate', function(){
            var laDate = $('#dateForum').val();
            $('.postForum').removeClass('active');
            if (laDate != '') {
                Cookies.set('dateForum', laDate, { expires: 7 });
                $('#btn-date #laDate').text(laDate);
                var laDate = laDate.substr(0,5);
                $('.postForum[data-date="' + laDate +'"]').addClass('active');
                }
                else {
                    $('#btn-date #laDate').text('Date');
                }
            $('#modalDate').modal('hide');
        })

        $('#btn-hideRepondre').click(function(){
            $('.repondre').toggle('hidden');
        })

        $('#listePosts').on('click', '.postForum', function(){
            $(this).toggleClass('active');
        })

        // effacement d'un post
        $('#listePosts').on('click', '.btn-delPost', function(){
            var postId = $(this).data('postid');
            var idCategorie = $(this).data('idcategorie');
            var idSujet = $(this).data('idsujet');
            $.post('inc/forums/modalDelPost.inc.php', {
                postId: postId,
                idCategorie: idCategorie,
                idSujet: idSujet
            }, function(resultat){
                $('#modal').html(resultat);
                $('#modalDelPost').modal('show');
            })
        })

        $('#modal').on('click', '#btn-confirmDelPost', function(){
            var postId = $(this).data('postid');
            var formulaire = $('#formModalDelPost').serialize();
            $.post('inc/forums/delPost.inc.php', {
                formulaire: formulaire
            }, function(resultat){
                if (resultat == 1) {
                    $('.postForum[data-postid="' + postId + '"]').html("<span class='supprime'>Cette contribution a été supprimée</span>");
                    $('.repondre [data-postid="' + postId + '"].btn-forum').attr('disabled', true);
                }
                else {
                    $('.postForum[data-postid="' + postId + '"]').remove();
                    $('.repondre[data-postid="' + postId + '"]').remove();
                }
                $('#modalDelPost').modal('hide');
            })
        })

        $('#modal').on('click', '#saveNewPost', function(){
            if($('#formModalAnswer').valid()){
                var idCategorie = $('#formModalAnswer #idCategorie').val();
                var idSujet = $('#formModalAnswer #idSujet').val();
                var formulaire = $('#formModalAnswer').serialize();
                $.post('inc/forums/saveAnswer.inc.php', {
                    formulaire: formulaire
                }, function(resultat){
                    $('.btn-sujet[data-idcategorie="' + idCategorie + '"][data-idsujet="' + idSujet + '"]').trigger('click');
                    // envoi des notifications éventuelels par mail
                    $.post('inc/forums/mail4NewPost.inc.php', {
                        formulaire: formulaire
                    }, function(){
                    });
                    $('#modalAnswerPost').modal('hide');
                })
            }
        })

        $('#listePosts').on('click', '#racinePosts', function(){
            var postId = $(this).data('postid');
            var idCategorie = $(this).data('idcategorie');
            var idSujet = $(this).data('idsujet');
            $.post('inc/forums/getModalAnswer.inc.php', {
                postId: postId,
                idCategorie: idCategorie,
                idSujet: idSujet
            }, function(resultat){
                $('#modal').html(resultat);
                $('#modalAnswerPost').modal('show');
            })
        })

        $('#listePosts').on('click', '.btn-repondre', function(){
            var postId = $(this).data('postid');
            var idCategorie = $(this).data('idcategorie');
            var idSujet = $(this).data('idsujet');
            $.post('inc/forums/getModalAnswer.inc.php', {
                postId: postId,
                idCategorie: idCategorie,
                idSujet: idSujet
            }, function(resultat){
                $('#modal').html(resultat);
                $('#modalAnswerPost').modal('show');
            })
        })

        $('#listePosts').on('click', '.btn-edit', function() {
            var postId = $(this).data('postid');
            var idCategorie = $(this).data('idcategorie');
            var idSujet = $(this).data('idsujet');
            $.post('inc/forums/getModalModify.inc.php', {
                postId: postId,
                idCategorie: idCategorie,
                idSujet: idSujet
            }, function(resultat){
                $('#modal').html(resultat);
                $('#modalModify').modal('show');
            })
        })

        $('#modal').on('click', '#saveEditedPost', function(){
            if($('#formModalModify').valid()){
                var idCategorie = $('#formModalModify #idCategorie').val();
                var idSujet = $('#formModalModify #idSujet').val();
                var formulaire = $('#formModalModify').serialize();
                $.post('inc/forums/saveEditedPost.inc.php', {
                    formulaire: formulaire
                }, function(resultat){
                    $('.btn-sujet[data-idcategorie="' + idCategorie + '"][data-idsujet="' + idSujet + '"]').trigger('click');
                    $('#modalModify').modal('hide');
                })
            }
        })

        $('#listeSujets').on('click', '.btn-sujet', function(){
            var idCategorie = $(this).data('idcategorie');
            var idSujet = $(this).data('idsujet');
            var sujet = $(this).data('sujet');
            $('#titreSujet').text(sujet);
            $('#btn-date').attr('disabled', false);
            $('.btn-sujet').removeClass('active');
            $(this).addClass('active');
            $.post('inc/forums/getListePosts.inc.php', {
                idCategorie: idCategorie,
                idSujet: idSujet
            }, function(resultat){
                $('#listePosts').html(resultat);
                var dateForum = Cookies.get('dateForum');
                if (dateForum != undefined) {
                    var dateForum = dateForum.substr(0,5);
                    $('.postForum[data-date="' + dateForum +'"]').addClass('active');
                }
                var texteSuivi = Cookies.get('texteSuivi');
                if (texteSuivi == "false"){
                    $('.postForum').addClass('em8');
                }
            })
        })

    })

</script>
