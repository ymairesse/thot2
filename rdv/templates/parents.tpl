<h3>Inviter mes parents</h3>

<p>Tu peux inviter un maximum de deux parents.</p>

<ul class="nav nav-pills">
    <li {if !isset($onglet)}class="active"{/if}><a data-toggle="tab" href="#parents">Parents invités</a></li>
    <li {if (isset($onglet)) && ($onglet == 'inviter')}class="active"{/if}><a data-toggle="tab" href="#invitation">Inviter un parent</a></li>
</ul>

<div class="tab-content">
    <div id="parents" class="tab-pane fade {if !isset($onglet)} in active{/if}">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                   Parent(s) invité(s)
                </h4>
            </div>
                <div class="panel-body">
                    {include file="parents/listeParents.tpl"}
                </div>
        </div>
    </div>

    <div id="invitation" class="tab-pane fade {if (isset($onglet)) && ($onglet == 'inviter')}in active{/if}">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">
                    Inviter un parent
                </h4>
            </div>
            <div class="panel-body">
                {if $listeParents|count < 2}
                    {include file="parents/formulaireParents.tpl" } {else} <p>Tu as déjà invité deux parents.</p>
                {/if}
            </div>
        </div>
    </div>

</div>


{if isset($motifRefus) && ($motifRefus != '')}
<div id="motifRefus" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">Problème</h4>
            </div>

            <div class="modal-body">
                <p>{$motifRefus}</p>
                <p>Veuillez corriger</p>
                <p class="text-danger"><i class="fa fa-warning fa-lg"></i> Les données ne sont pas enregistrées</p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Fermer cette fenêtre</button>
            </div>

        </div>
        <!-- modal-content  -->
    </div>
    <!-- modal-dialog -->
</div>
<!-- motifRefus -->

<script type="text/javascript">
    $(document).ready(function() {
        $("#motifRefus").modal('show');
    })
</script>
{/if}
