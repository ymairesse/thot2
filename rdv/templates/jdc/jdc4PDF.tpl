<style>
  td,
  th {
    border: inset 1px #555;
    padding: 5px;
    font-size: 9pt;
  }
  p {
      margin: 3px;
  }
  h1, h2, h3 {
      font-size: 12pt;
  }
</style>

<page backtop="25mm" backbottom="10mm" backleft="7mm" backright="45mm"  footer="page; date">
     <page_header>
       <img src="../../images/logoEcole.png" alt="LOGO" style="float:right">
       <p>{$ECOLE}
         <br>{$ADRESSE} {$COMMUNE}
         <br>Téléphone: {$TELEPHONE}</p>
     </page_header>
     <page_footer>
        {$userName}
     </page_footer>

    <h3>Journal de classe: {if $dateDebut != ''} du {$dateDebut}{/if}
        {if $dateFin != ''} au {$dateFin}{/if}</h3>

    <table>
        <tr>
            <th style="width:10%">Date</th>
            <th style="width:80%">Note</th>
        </tr>
        {foreach from=$jdcExtract key=id item=data}
        <tr {if (in_array($data.idCategorie, array('1','2','5')))} style="background:#faa"{/if}">
            <td><strong>{$data.categorie}</strong><br>
                <strong>{$data.dest}</strong> : {$data.proprietaire}<br>
                {$data.startDate}<br>
                {if $data.startHeure != '00:00:00'}{$data.startHeure|truncate:5:''}{/if}
                {if $data.endHeure != $data.startHeure} - {$data.endHeure|truncate:5:''}{/if}
            </td>
            <td>{$data.enonce}</td>
        </tr>
        {/foreach}
    </table>

</page>
