<div class="panel-group listeAnnonces" id="accordion">

    <div class="panel panel-default" id="panel-eleve">

        <div class="panel-heading">
            <h4 class="panel-title">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapse1">{if isset($nomEleve)}{$nomEleve}{else}{$nom}{/if}
                    {if $shortListeAccuses.eleves > 0}
                        <i class="fa fa-warning pull-right"></i>
                    {/if}
                    {assign var=nb value=$listeAnnonces.eleves|@count|default:0}
                    <span id="nbAccuse_eleve" class="badge pull-right" data-type="eleve" data-nbaccuse="{$nb}">{$nb}</span>
              </a>
            </h4>
        </div>
        <!-- panel-heading -->

        <div id="collapse1" class="panel-collapse collapse in annonces personnel">
            <div class="panel-body">
                {if isset($listeAnnonces.eleves)}
                <ul>
                    {foreach from=$listeAnnonces.eleves key=id item=uneAnnonce}
                    <li class="dymo urgence{$uneAnnonce.urgence}">
                        {if ($uneAnnonce.accuse == 1) && ($uneAnnonce.dateHeure == '')}
                        <i id="warning{$id}" class="fa fa-warning fa-lg faa-flash animated danger" style="color:red; padding-right:0.5em;"></i> {/if}
                        <a href="#note_{$id}" title="{$uneAnnonce.dateDebut}">{$uneAnnonce.objet|truncate:30}</a>
                    </li>
                    {/foreach}
                </ul>
                {else}
                <p>Néant</p>
                {/if}
            </div>
        </div>
        <!-- id=collapse1 -->

    </div>
    <!-- panel-eleve -->



    <div class="panel panel-default" id="panel-cours">

        <div class="panel-heading">
            <h4 class="panel-title">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapse2">Cours
                    {if $shortListeAccuses.cours > 0}
                        <i class="fa fa-warning pull-right"></i>
                    {/if}
                    {assign var=nb value=$listeAnnonces.cours|@count|default:0}
                    <span id="nbAccuse_eleve" class="badge pull-right" data-type="eleve" data-nbaccuse="{$nb}">{$nb}</span>
              </a>
            </h4>
        </div>
        <!-- panel-heading -->

        <div id="collapse2" class="panel-collapse collapse annonces cours">
            <div class="panel-body">
                {if isset($listeAnnonces.cours)}
                <ul>
                    {foreach from=$listeAnnonces.cours key=id item=uneAnnonce}
                    <li class="dymo urgence{$uneAnnonce.urgence}">
                        {if ($uneAnnonce.accuse == 1) && ($uneAnnonce.dateHeure == '')}
                        <i id="warning{$id}" class="fa fa-warning fa-lg faa-flash animated danger" style="color:red; padding-right:0.5em;"></i> {/if}
                        <a href="#note_{$id}" title="{$uneAnnonce.dateDebut}">{$uneAnnonce.objet|truncate:30}</a>
                    </li>
                    {/foreach}
                </ul>
                {else}
                <p>Néant</p>
                {/if}
            </div>
        </div>
        <!-- id=collapse1 -->

    </div>
    <!-- panel-eleve -->


    <div class="panel panel-default" id="panel-classe">

        <div class="panel-heading">
            <h4 class="panel-title">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapse3">Classe {$classe}
              {if $shortListeAccuses.classes > 0}
              <i class="fa fa-warning pull-right"></i>
              {/if}
              {assign var=nb value=$listeAnnonces.classes|@count|default:0}
              <span id="nbAccuse_classe" class="badge pull-right" data-type="classe" data-nbaccuse="{$nb}">{$nb}</span>
              </a>
            </h4>
        </div>

        <div id="collapse3" class="panel-collapse collapse annonces classe">

            <div class="panel-body">
                {if isset($listeAnnonces.classes)}
                <ul>
                    {foreach from=$listeAnnonces.classes key=id item=uneAnnonce}
                    <li class="dymo urgence{$uneAnnonce.urgence}">
                        {if ($uneAnnonce.accuse == 1) && ($uneAnnonce.dateHeure == '')}
                        <i id="warning{$id}" class="fa fa-warning fa-lg faa-flash animated danger" style="color:red; padding-right:0.5em;"></i> {/if}
                        <a href="#note_{$id}" title="{$uneAnnonce.dateDebut}">{$uneAnnonce.objet|truncate:30}</a>
                    </li>
                    {/foreach}
                </ul>
                {else}
                <p>Néant</p>
                {/if}
            </div>

        </div>
    </div>
    <!-- panel-default -->


    <div class="panel panel-default" id="panel-niveau">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapse4">
              {if $shortListeAccuses.niveau > 0}
              <i class="fa fa-warning pull-right"></i>
              {/if}
              {assign var=nb value=$listeAnnonces.niveau|@count|default:0}
              Élèves de {$niveau}<sup>e</sup>
              <span id="nbAccuse_niveau" class="badge pull-right" data-type="niveau" data-nbaccuse="{$nb}">{$nb}</span></a>
            </h4>
        </div>

        <div id="collapse4" class="panel-collapse collapse annonces niveau">

            <div class="panel-body">

                {if isset($listeAnnonces.niveau)}
                <ul>
                    {foreach from=$listeAnnonces.niveau key=id item=uneAnnonce}
                    <li class="dymo urgence{$uneAnnonce.urgence}">
                        <a href="#note_{$id}" title="{$uneAnnonce.dateDebut}">{$uneAnnonce.objet|truncate:30}</a> {if ($uneAnnonce.accuse == 1) && ($uneAnnonce.dateHeure == '')}
                        <i id="warning{$id}" class="fa fa-warning fa-lg faa-flash animated danger" style="color:red; padding-right:0.5em;"></i> {/if}
                    </li>
                    {/foreach}
                </ul>
                {else}
                <p>Néant</p>
                {/if}

            </div>

        </div>
    </div>
    <!-- panel-default -->

    <div class="panel panel-default" id="panel-ecole">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapse5">
                {if $shortListeAccuses.ecole > 0}
                <i class="fa fa-warning pull-right"></i>
                {/if}
                {assign var=nb value=$listeAnnonces.ecole|@count|default:0}
                Tous <span id="nbAccuse_ecole" class="badge pull-right" data-type="ecole" data-nbaccuse="{$nb}">{$nb}</span></a>
            </h4>
        </div>

        <div id="collapse5" class="panel-collapse collapse annonces ecole">

            <div class="panel-body">

                {if isset($listeAnnonces.ecole)}
                <ul>
                    {foreach from=$listeAnnonces.ecole key=id item=uneAnnonce}
                    <li class="dymo urgence{$uneAnnonce.urgence}">
                        <a href="#note_{$id}" title="{$uneAnnonce.dateDebut}">{$uneAnnonce.objet|truncate:30}</a> {if ($uneAnnonce.accuse == 1) && ($uneAnnonce.dateHeure == '')}
                        <i id="warning{$id}" class="fa fa-warning fa-lg faa-flash animated danger" style="color:red; padding-right:0.5em;"></i> {/if}
                    </li>
                    {/foreach}
                </ul>
                {else}
                <p>Néant</p>
                {/if}

            </div>

        </div>
    </div>
    <!-- panel-default -->

</div>
<!-- accordion -->
