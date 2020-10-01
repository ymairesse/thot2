<div class="panel panel-default" id="panel1">

    <div class="panel-heading">
        <h2 class="panel-title">
            <i class="fa fa-graduation-cap"></i>
            <a data-toggle="collapse" data-target="#collapseOne" href="#collapseOne">Accès aux bulletins, au journaux de classe et aux annonces</a>
        </h2>
    </div>

    <div id="collapseOne" class="panel-collapse collapse in">

        <div class="panel-body">

            <div class="col-md-9 col-xs-12">

                <fieldset>
                    <legend>Veuillez vous identifier</legend>
                    <!-- <p>Cette plate-forme est strictement réservée aux élèves de l'<a href="http://secondaire.isnd.be" target="_blank">ISND</a> et leurs parents.</p> -->

                    <div class="col-md-6 col-sm12" style="background: #afa; border: 2px solid white; padding-bottom:1em;">

                        <form autocomplete="off" role="form" class="form-vertical" method="POST" id="loginEleves" action="login.php" id="formLogin">

                            <h3>Elèves</h3>

                            <div class="form-group">
                                <p>
                                    <img src="images/eleves.png" alt="eleves" style="float:left; padding: 0.5em">Nom d'utilisateur: contient la première lettre du prénom, sept lettres du nom et 4 chiffres.</p>
                                <label for="userName" class="sr-only">Utilisateur</label>
                                <input type="text" required name="userName" id="userName" tabindex="1" placeholder="Nom d'utilisateur" class="pop" data-content="Nom d'utilisateur, y compris les <span style='color:red'>4 chiffres.</span>. " data-html="true" data-placement="top">
                            </div>
                            <!-- form-group -->

                            <div class="form-group">
                                <label for="mdp" class="sr-only">Mot de passe</label>
                                <input name="mdp" required id="mdp" type="password" tabindex="2" placeholder="Mot de passe" class="pop" data-content="Mot de passe" data-html="true" data-placement="top">
                            </div>
                            <!-- form-group -->

                            <button type="submit" class="btn btn-primary" tabindex="3">Connexion Élèves</button>
                            <input type="hidden" name="userType" value="eleves">

                        </form>

                    </div>
                    <!-- class-col-md... -->

                    <div class="col-md-6 col-sm12" style="background:#aaf; border: 2px solid white; padding-bottom:1em;">

                        <form autocomplete="off" role="form" class="form-vertical" method="POST" id="loginParents" action="login.php" id="formLogin">

                            <h3>Parents</h3>

                            <div class="form-group">
                                <p>
                                    <img src="images/parents.png" alt="eleves" style="float:right; padding: 0.5em">Le nom d'utilisateur choisi terminé par les 4 chiffres du matricule de l'élève.</p>
                                <label for="userName" class="sr-only">Utilisateur</label>
                                <input type="text" required name="userName" id="userNameParent" tabindex="3" placeholder="Nom d'utilisateur" class="pop" data-content="Le nom d'utilisateur que vous avez choisi, y compris les <span style='color:red'>4 chiffres</span> du matricule de l'élève. "
                                data-html="true" data-placement="top">
                            </div>
                            <!-- form-group -->

                            <div class="form-group">
                                <label for="mdp" class="sr-only">Mot de passe</label>
                                <input name="mdp" required id="mdpParent" type="password" tabindex="4" placeholder="Mot de passe" class="pop" data-content="Le mot de passe que vous avez choisi" data-html="true" data-placement="top">
                            </div>
                            <!-- form-group -->

                            <a href="mdp/index.php">Mot de passe oublié</a>

                            <button type="submit" class="btn btn-primary pull-right" tabindex="5">Connexion Parents</button>
                            <input type="hidden" name="userType" value="parents">

                        </form>

                    </div>

                    <div class="info">
                        <p><i class="fa fa-info-circle fa-lg" style="color:#55f"></i> Les élèves doivent "inviter" leurs parents pour leur ouvrir un accès à l'application. Chaque élève peut "inviter" jusqu'à 2 parents ayant chacun un nom d'utilisateur et
                            un mot de passe personnel.
                            <br> Voir le menu "J'invite mes parents" dans la plate-forme "élèves".</p>
                    </div>
                </fieldset>
                </form>

            </div>

            <div class="col-md-3 col-xs-4">
                <div class="img-responsive">
                    <img src="images/thot.png" alt="thot">
                </div>

            </div>
            <!-- col-md-... -->

        </div>
        <!-- panel-body -->

    </div>
    <!-- panel-collapse -->

</div>
<!-- panel 1 -->


<script type="text/javascript">
    $(document).ready(function() {

                $("#loginParents").validate();

                $("#loginEleves").validate();

            }
</script>
