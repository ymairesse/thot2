{if $detailsTravail != Null}

    <ul class="nav nav-pills nav-justified">
      <li {if $onglet == 'consignes'}class="active"{/if}><a data-toggle="pill" href="#consigne" class="onglet" data-onglet="consignes"><i class="fa fa-info-circle fa-lg"></i> Consignes et dépôt</a></li>
      <li {if $onglet == 'evaluation'}class="active"{/if}><a data-toggle="pill" href="#evaluation" class="onglet" data-onglet="evaluation"><i class="fa fa-graduation-cap fa-lg"></i> Évaluation {if $listeCotes|@count > 0}<span class="badge">{$listeCotes|count}</span>{/if}</a></li>
    </ul>

    <div class="tab-content">
      <div id="consigne" class="tab-pane fade {if $onglet == 'consignes'}in active{/if}">
        <h3>Consignes [{$detailsTravail.titre}]</h3>
        <div class="panel panel-info">
            <div class="panel-body">
                <div id="blocConsignes" class="form-control-static texte">
                    {$detailsTravail.consigne}
                </div>
            </div>
            <div class="panel-footer">
                <p>Date de début: <strong>{$detailsTravail.dateDebut}</strong> | Date de fin: <strong>{$detailsTravail.dateFin}</strong></p>
            </div>
        </div>

        <div class="panel panel-success">
            <div class="panel-body">
                <div class="col-md-9 col-xs-12">
                    <div id="fileInfos">
                        {include file='casiers/detailsUpload.inc.tpl'}
                    </div>
                </div>

                <div class="col-md-3 col-xs-12">
                    {* bouton dépôt ou pas? *}
                    {if $detailsTravail.fileInfos.fileName == '' && $detailsTravail.statut == 'readwrite'}
                      <button type="button" class="btn btn-success btn-lg btn-block" id="callDropZone"><i class="fa fa-envelope-o fa-2x" style="float:left"></i> Déposer <br>mon travail</button>
                    {/if}
                </div>

                <div class="col-md-9 col-xs-12">
                    <div class="form-group">
                        <label for="remarque">Remarque pour le professeur</label>
                        {if in_array($detailsTravail.statut, array('termine', 'readonly', 'archive')) || $totalTravail.cote != Null}
                            <p class="form-control-static">{$detailsTravail.remarque|default:'-'}</p>
                            {else}
                            <input type="text" name="remarque" id="remarque" class="form-control" maxlength="80" value="{$detailsTravail.remarque}">
                        {/if}
                        <p class="help-block">Une courte information que je voudrais ajouter (pas obligatoire, max 80 car.)</p>
                    </div>
                </div>

                <div class="col-md-3 col-xs-12">
                    {if !(in_array($detailsTravail.statut, array('termine', 'readonly', 'archive'))) && $totalTravail.cote == Null}
                        <button type="button" class="btn btn-primary btn-block" id="saveRemarque" style="margin-top:2em;"><i class="fa fa-save fa-2x"></i> Enregistrer</button>
                    {/if}
                </div>
            </div>
        </div>
      </div>

      <div id="evaluation" class="tab-pane fade {if $onglet == 'evaluation'}in active{/if}">
        <h3>Évaluation  [{$detailsTravail.titre}]</h3>
        <div class="panel panel-danger">

            <div class="panel-body">
                {* liste des cotes, compétences, max *}
                  <table class="table table-condensed">
                      <tr>
                          <th>Points par compétences</th>
                          <th>Type</th>
                          <th style="width:5em; text-align:center">Points</th>
                          <th style="width:5em; text-align:center">Max</th>
                      </tr>
                      {foreach from=$listeCotes key=idCompetence item=data}
                      <tr>
                          <td>{$data.libelle}</td>
                          <td>{if $data.formCert == 'cert'}Certificatif{else}Formatif{/if}</td>
                          <td style="text-align:center">{$data.cote}</td>
                          <td style="text-align:center">{$data.max}</td>
                      </tr>
                      {/foreach}
                  </table>

                  {* évaluation du professeur *}
                  <div class="form-group">
                      <label for="evaluation">Avis du professeur</label>
                      <div id="evaluation" class="form-control-static texte">
                          {$detailsTravail.evaluation|default:'---'}
                      </div>
                      <p class="help-block">Notes du professeur</p>
                  </div>

            </div>
        </div>
      </div>
    </div>

{else}
    <p class="avertissement">Sélectionnez un travail dans la colonne de gauche</p>
{/if}
