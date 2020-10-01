{function name=repertoire level=0}

<ul class="" data-level="{$level}">

    {foreach $data as $categorie}
        {assign var=idCategorie value=$categorie.idCategorie}
        {if isset($categorie.children)}
            <li class="{$categorie.userStatus}" data-idcategorie="{$categorie.idCategorie}">
                <a href="javascript:void(0)"
                    data-idcategorie="{$categorie.idCategorie}"
                    data-userstatus="{$categorie.userStatus}">
                {$categorie.libelle}
                </a>

                {repertoire data=$categorie.children level=$level+1}

            </li>
        {else}
            <li class="{$categorie.userStatus}" data-idcategorie="{$categorie.idCategorie}">
                <a href="javascript:void(0)"
                    data-idcategorie="{$categorie.idCategorie}"
                    data-userstatus="{$categorie.userStatus}">
                {$categorie.libelle}
                </a>
            </li>
        {/if}
    {/foreach}
</ul>

{/function}

<ul class="treeview" style="max-height:30em; overflow: auto;">

    <li>
        <a class="active hidden"
            href="javascript:void(0)"
            data-idcategorie="0"
            data-userstatus="racine">
            Racine
        </a>
    </li>

    {repertoire data=$listeCategories}

</ul>

<script type="text/javascript" src="js/treeview.js"></script>

<script type="text/javascript">


    $(document).ready(function(){

        $('.treeview').treeview();

        $('#treeviewCategories li a').click(function(){
            $('#treeviewCategories li a').removeClass('active');
            var idCategorie = $(this).data('idcategorie');
            $(this).addClass('active');
        })

    })

</script>
