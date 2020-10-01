<div class="panel-group" id="forms">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a data-toggle="collapse" data-parent="#" href="#">Formulaires</a>
            </h4>
        </div>

        {foreach from=$listeFormulaires key=id item=formulaire}

        <div id="form_{$id}" class="panel-collapse collapse in">
            <div class="panel-body">
                {if isset($listeReponses[$id])}
                <div class="alert alert-warning">Vous avez déjà répondu à ce formulaire</div>
                {/if}
                <form action="index.php" method="POST" role="form" class="form-vertical">

                    <h2>{$formulaire.titre}</h2>
                    <div class="col-md-6 col-sm-12">
                        <div class="alert alert-info">
                            {$formulaire.explication}
                        </div>
                    </div>
                    <div class="col-md-6 col-sm-12">
                        {foreach from=$listeQuestions[$id] item=uneQuestion}
                        {assign var=numQ value=$uneQuestion.numQuestion}
                        {if $uneQuestion.type == 'select'}

                        <input type="hidden" name="typeQ_{$numQ}" value="{$uneQuestion.type}">
                        <div class="form-group alert alert-success">
                            <h4>{$uneQuestion.question}</h4>

                            {assign var=choix value=$listeReponses.$id.$numQ|default:null}
                            <select class="form-control" id="Q_{$numQ}" name="Q_{$numQ}">
                                <option value=''>Sélectionner</option>
                                {foreach from=$uneQuestion.reponses item=reponse}
                                <option value={$reponse@index} {if ($choix != Null) && ($choix == $reponse@index)}selected{/if}>
                                    {$reponse}
                                </option>
                                {/foreach}
                            </select>

                        </div>

                        {elseif $uneQuestion.type == 'checkbox'}

                        <input type="hidden" name="typeQ_{$numQ}" value="{$uneQuestion.type}">
                        <div class="form-group alert alert-success">
                            <h4>{$uneQuestion.question}</h4>
                            <fieldset {if isset($listeReponses[$id])}disabled{/if}>
                            <ul class="list-unstyled">
                                {foreach from=$uneQuestion.reponses key=numReponse item=reponse}
                                <li>
                                    <input type="checkbox"
                                            name="R[{$numQ}][]"
                                            value="{$numReponse}"
                                            class="form-control-inline"
                                            {if isset($listeReponses[$id]) && in_array($numReponse,$listeReponses[$id][$numQ])} checked{/if}>
                                        {$reponse}
                                </li>
                                {/foreach}

                            </ul>
                            </fieldset>
                        </div>


                        {/if}
                        {/foreach}
                        <input type="hidden" name="action" value="form">
                        <input type="hidden" name="mode" value="enregistrer">
                        <input type="hidden" name="form_id" value="{$id}">
                        <div class="btn-group pull-right">
                            <button type="reset" class="btn btn-default">Annuler</button>
                            <button type="submit" class="btn btn-primary" {if isset($listeReponses[$id])}disabled{/if}>Enregistrer</button>
                        </div>

                    </div>
                    <!-- col-md-... -->
                </form>

                <script type="text/javascript">
                    $(document).ready(function() {

                        $("#form_{$id}").validate();

                    })
                </script>

            </div>
            <!-- panel-body -->

        </div>
        {/foreach}
    </div>
</div>
