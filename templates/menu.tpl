<nav class="navbar navbar-default{if $userType =='parents'} parents{/if}" role="navigation">

	<div class="navbar-header">

		<button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#barreNavigation">
			<span class="sr-only">Navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>

		<a class="navbar-brand" href="index.php"><i class="fa fa-home"></i></a>

	</div>
	<!-- navbar-header -->

	<div class="collapse navbar-collapse" id="barreNavigation">

		<ul class="nav navbar-nav">
			<li><a href="index.php?action=annonces"><i class="fa fa-info-circle" style="color:orange"></i> Annonces</a></li>
			<li><a href="index.php?action=documents"><i class="fa fa-folder-open-o" style="color:red"></i> Thot <br>Docs</a></li>
			<li class="dropdown"><a class="dropdown-toogle" data-toggle="dropdown" href="javascript:void(01)"><i class="fa fa-graduation-cap" style="color:blue"></i> Résultats<br>scolaires <b class="caret"></b></a>
				<ul class="dropdown-menu">
					<li><a href="index.php?action=bulletin">Bulletins</a></li>
					<li><a href="index.php?action=repertoire">Répertoire des évaluations</a></li>
					<li><a href="index.php?action=remediation">Remédiations</a></li>
				</ul>
			</li>
			{if $userType == 'eleve'}
			<li><a href="index.php?action=anniversaires"><i class="fa fa-birthday-cake" style="color:red"></i> Anniversaires</a></li>
			{/if}
			<li><a href="index.php?action=casiers"><i class="fa fa-inbox"></i> Casiers<br>Virtuels</a></li>

			<li class="dropdown">
				<a class="dropdown-toogle" data-toggle="dropdown" href="javascript:void(0)" id="menuJdc">
					<i class="fa fa-newspaper-o" style="color:#4AB23A"></i> JDC<b class="caret"></b>
				</a>
				<ul class="dropdown-menu">
					<li><a href="index.php?action=jdc" id="linkJdc">Mon JDC</a></li>
					<li><a href="index.php?action=jdc&amp;mode=horaire">Mon horaire journalier</a></li>
					<li><a href="index.php?action=jdc&amp;mode=perso">Notes personnelles</a></li>
				</ul>
			</li>

			<li class="dropdown">
				<a class="dropdown-toogle" data-toggle="dropdown" href="javascript:void(0)" id="menuForum">
					<i class="fa fa-comment" style="color:#cc0"></i> Forums<b class="caret"></b>
				</a>
				<ul class="dropdown-menu">
					<li><a href="index.php?action=forums&amp;mode=forum" id="linkForum">Accéder aux forums</a></li>
					<li><a href="index.php?action=forums&amp;mode=gestion">Gérer mes abonnements</a></li>
				</ul>
			</li>

			{if $userType == 'eleve'}
			<li>
				<a href="index.php?action=parents"><i class="fa fa-users" style="color:#EAA6B1"></i> Parents</a>
			</li>
			{/if}

			<li>
				<a href="index.php?action=comportement"><i class="fa fa-pencil" style="color:#55aaaa"></i> Comportement</a>
			</li>

			{if $userType == 'eleve'}
			<li><a href="index.php?action=mails"><i class="fa fa-send-o"></i> Communiquer</a></li>
			{/if}

			{if $userType == 'parent'}
			<li class="dropdown">
				<a class="dropdown-toogle" data-toggle="dropdown" href="javascript:void(01)"><i class="fa fa-user" style="color:blue"></i> Profil<b class="caret"></b></a>
				<ul class="dropdown-menu">
					<li><a href="index.php?action=profil"><i class="fa fa-user" style="color:#EAA6B1"></i> Profil personnel</a></li>
					<li><a href="index.php?action=frereSoeur"><i class="fa fa-users" style="color:#666"></i> Frères et sœurs</a></li>
				</ul>
			</li>
			{/if}

			<li>
				<a href="index.php?action=contact"><i class="fa fa-envelope-o" style="color:#ff0000"></i> Contact</a>
			</li>

			{if $userType == 'parent'}
			<li>
				<a href="index.php?action=reunionParents"><i class="fa fa-calendar" style="color:#16931b"></i> Réunion de parents</a>
			</li>
			{/if}

			<li><a href="index.php?action=info" title="Informations"><i class="fa fa-info-circle" style="color:blue"></i></a></li>
		</ul>

		<ul class="nav navbar-nav pull-right">

			<li class="dropdown">
				<a href="#" data-toggle="dropdown">
					<span id="leNom" data-user="{$identite.userName|default:$identite.user}">{$identite.prenom} {$identite.nom}</span> <br>
					<span id="userNameLog">
					{if isset($identite.user)}[{$identite.user}] {else}[{$identite.userName}]{/if} <b class="caret"></b>
					</span>
				</a>

				<ul class="dropdown-menu">
					<li>
						<a href="index.php?action=logoff">
							<span class="glyphicon glyphicon-off">&nbsp;</span>Se déconnecter</a>
					</li>
				</ul>
			</li>

		</ul>

	</div>
	<!-- #barreNavigation -->

</nav>

<script type="text/javascript">

	$(document).ready(function(){
		$('#menuJdc').click(function(){
			$('#linkJdc').trigger('click');
		})
	})

</script>
