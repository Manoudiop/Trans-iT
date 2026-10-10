<?= $this->extend('layouts/no_js'); ?>
<?= $this->section('title'); ?>
Facture Dossier Nº <?= $id ?>
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>

<style>
  @media print {
    .btn {
      display: none;
    }

    body {
      font-size: 10px;
    }
  }
</style>


<div class="container bg-white">

  <button type="button" onclick="window.print()" class="btn btn-primary mt-2">
    Imprimer
  </button>


  <div class="d-flex justify-content-between align-items-center py-3">
    <div>
      <?php // public/logo.png n'existe pas: afficher la balise imprimait une
      // image cassée sur la facture remise au client. ?>
      <?php if (is_file(FCPATH . "logo.png")) : ?>
        <img src="<?= base_url("logo.png") ?>" height="100px" width="100px" alt="Logo">
      <?php endif ?>
    </div>
    <div class=" flex-grow-1 text-center" style="max-width: 400px;">
      <div class="h1 mb-0"><?= esc($agence["name"] ?? "") ?></div>
      <div class="h1 mb-0"><?= $type == "EXP" ? "EXPORT" : "IMPORT" ?></div>
    </div>
  </div>

  <div class="row">
    <div class="col-12 text-center">
      <h1>Facture Nº <span class="text-primary"><?= $reference ?></span> </h1>
    </div>
    <div class="col-12">
      <div class="row">
        <div class="col">
          <?php
          // strtotime(null) vaut 0, donc une date vide s'imprimait
          // « 01/01/1970 » sur la facture remise au client.
          $leDate = static fn ($valeur): string => empty($valeur) ? "-" : date("d/m/Y", strtotime($valeur));
          ?>
          <div class="mb-2"><small>CNT/LTA</small> <br> <strong><?= $bl ?></strong> du <strong><?= $leDate($bl_of) ?></strong></div>
          <div class="mb-2"><small>Vol/Navire</small> <br> <strong><?= $boat ?></strong> du <strong><?= $leDate($boat_of) ?></strong></div>
        </div>
        <div class="col">
          <div class="mb-2"><small>Nombre de colis</small> <br> <strong><?= $items_count ?></strong></div>
          <div class="mb-2"><small>Poids total en Kg</small> <br> <strong><?= $total_weight ?></strong></div>
        </div>
        <div class="col">
          <div class="mb-2"><small>Clients</small> <br> <strong><?= esc($invoice_to["id"]) ?> <?= esc($invoice_to["name"]) ?></strong></div>
          <?php // Le poids total figurait deux fois, ici et dans la colonne des colis.
          // La déclaration, elle, est remontée du bas de l'en-tête: c'est la
          // référence que le client cite, elle se lit avec son nom. ?>
          <div class="mb-2"><small>Déclaration</small> <br> <strong><?= esc($declaration ?: "-") ?></strong></div>
        </div>
        <div class="col-12">
          <div class="mb-2"><small>Désignation</small> <br> <strong><?= esc($designation) ?></strong></div>
        </div>
      </div>
    </div>
    <hr class="mt-2" style="border: solid 2px black;opacity:1">
    <div class="col-12">
      <h3 class="text-center">RÉPERTOIRE</h3>
      <table id="merchTable" class="table table-vcenter table-sm text-sm">
        <tbody>
          <tr>
            <th colspan="2">DÉBOURS</th>
          </tr>
          <?php if ($duties_taxes > 0) : ?>
            <tr>
              <td width="100%">Droits et Taxes (déclaration jointe)</td>
              <td class=" text-nowrap"><?= number_format($duties_taxes, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($agios) : ?>
            <tr>
              <td width="100%">Agios 1/1000</td>
              <td class=" text-nowrap"><?= number_format($agios, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($freight) : ?>
            <tr>
              <td width="100%">Frêt</td>
              <td class=" text-nowrap"><?= number_format($freight, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($bl_stamp) : ?>
            <tr>
              <td width="100%">Timbre de connaissement</td>
              <td class=" text-nowrap"><?= number_format($bl_stamp, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($shipping_taxe) : ?>
            <tr>
              <td width="100%">Taxes de port</td>
              <td class=" text-nowrap"><?= number_format($shipping_taxe, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($boarding_disembarkation) : ?>
            <tr>
              <td width="100%">Embarquement / Débarquement</td>
              <td class=" text-nowrap"><?= number_format($boarding_disembarkation, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($storing_guarding) : ?>
            <tr>
              <td width="100%">Magasinage / Gardiennage</td>
              <td class=" text-nowrap"><?= number_format($storing_guarding, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($container_transportation) : ?>
            <tr>
              <td width="100%">Transport / Conteneur</td>
              <td class=" text-nowrap"><?= number_format($container_transportation, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($handling) : ?>
            <tr>
              <td width="100%">Relevage sur parc / Engin de levage</td>
              <td class=" text-nowrap"><?= number_format($handling, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($insurance) : ?>
            <tr>
              <td width="100%">Assurance</td>
              <td class=" text-nowrap"><?= number_format($insurance, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($transportation) : ?>
            <tr>
              <td width="100%">Transport</td>
              <td class=" text-nowrap"><?= number_format($transportation, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($expert_report) : ?>
            <tr>
              <td width="100%">Rapport d'expertise</td>
              <td class=" text-nowrap"><?= number_format($expert_report, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($customs_excort) : ?>
            <tr>
              <td width="100%">Escorte Douane</td>
              <td class=" text-nowrap"><?= number_format($customs_excort, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($demurrage) : ?>
            <tr>
              <td width="100%">Surrestaries</td>
              <td class=" text-nowrap"><?= number_format($demurrage, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($customs_clearance) : ?>
            <tr>
              <td width="100%">Vacation Douane</td>
              <td class=" text-nowrap"><?= number_format($customs_clearance, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($postal_package_withdrawal_fees) : ?>
            <tr>
              <td width="100%">Frais de retraits de colis postaux</td>
              <td class=" text-nowrap"><?= number_format($postal_package_withdrawal_fees, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($customs_ts_visit) : ?>
            <tr>
              <td width="100%">T.S. Douane + Visite</td>
              <td class=" text-nowrap"><?= number_format($customs_ts_visit, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($full_land_rental) : ?>
            <tr>
              <td width="100%">Location terre plein (PAD)</td>
              <td class=" text-nowrap"><?= number_format($full_land_rental, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($visit_admissibility) : ?>
            <tr>
              <td width="100%">Visite / Recevabilité</td>
              <td class=" text-nowrap"><?= number_format($visit_admissibility, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($indirect_fees) : ?>
            <tr>
              <td width="100%">Taxes indirectes</td>
              <td class=" text-nowrap"><?= number_format($indirect_fees, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($orbus_fees) : ?>
            <tr>
              <td width="100%">DPI / Orbus</td>
              <td class=" text-nowrap"><?= number_format($orbus_fees, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <tr>
            <th class="text-end">Sous total:</th>
            <th class="text-nowrap"><?= number_format($duties_taxes + $agios + $freight + $bl_stamp + $shipping_taxe + $boarding_disembarkation + $storing_guarding + $container_transportation + $handling + $insurance + $transportation + $expert_report + $customs_excort + $demurrage + $customs_clearance + $postal_package_withdrawal_fees + $customs_ts_visit + $full_land_rental + $visit_admissibility + $indirect_fees + $orbus_fees, 2, ",", " ") ?> FCFA</th>
          </tr>

          <tr>
            <th colspan="2">INTERVENTIONS NON TAXABLES</th>
          </tr>
          <?php if ($trucking) : ?>
            <tr>
              <td width="100%">Camionnage</td>
              <td class=" text-nowrap"><?= number_format($trucking, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($grouping) : ?>
            <tr>
              <td width="100%">Mise en groupage</td>
              <td class=" text-nowrap"><?= number_format($grouping, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <tr>
            <th class="text-end">Sous total:</th>
            <th class="text-nowrap"><?= number_format($trucking + $grouping, 2, ",", " ") ?> FCFA</th>
          </tr>

          <tr>
            <th colspan="2">INTERVENTIONS TAXABLES</th>
          </tr>
          <?php if ($commission_on_disbursements) : ?>
            <tr>
              <td width="100%">Commission sur débours</td>
              <td class=" text-nowrap"><?= number_format($commission_on_disbursements, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($folder_opening_fees) : ?>
            <tr>
              <td width="100%">Ouverture de dossier</td>
              <td class=" text-nowrap"><?= number_format($folder_opening_fees, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($transit_commission) : ?>
            <tr>
              <td width="100%">Commission transit</td>
              <td class=" text-nowrap"><?= number_format($transit_commission, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($customs_honorary_fees) : ?>
            <tr>
              <td width="100%">Horaires d'agréé en Douane </td>
              <td class=" text-nowrap"><?= number_format($customs_honorary_fees, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($had) : ?>
            <tr>
              <td width="100%">H.A.D Ad Valorem</td>
              <td class=" text-nowrap"><?= number_format($had, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($internal_handling) : ?>
            <tr>
              <td width="100%">Manutension</td>
              <td class=" text-nowrap"><?= number_format($internal_handling, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($loading_unloading) : ?>
            <tr>
              <td width="100%">Empotage / Dépotage</td>
              <td class=" text-nowrap"><?= number_format($loading_unloading, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($printer) : ?>
            <tr>
              <td width="100%">Imprimés</td>
              <td class=" text-nowrap"><?= number_format($printer, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($procedures_formalities) : ?>
            <tr>
              <td width="100%">Démarches et formalités</td>
              <td class=" text-nowrap"><?= number_format($procedures_formalities, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <?php if ($tps) : ?>
            <tr>
              <td width="100%">T.P.S.</td>
              <td class=" text-nowrap"><?= number_format($tps, 2, ",", " ") ?> FCFA</td>
            </tr>
          <?php endif ?>
          <tr>
            <th class="text-end">Sous total:</th>
            <th class="text-nowrap"><?= number_format($commission_on_disbursements + $folder_opening_fees + $transit_commission + $customs_honorary_fees + $had + $internal_handling + $loading_unloading + $printer + $procedures_formalities + $tps, 2, ",", " ") ?> FCFA</th>
          </tr>
        </tbody>
      </table>
    </div>
    <hr class="mt-2" style="border: solid 2px black;opacity:1">
    <div class="text-end mb-5">
      <div>Arrête la facture au montant de:</div>
      <div class="display-5"><?= number_format($invoice_amount, 2, ",", " ") ?> FCFA</div>
    </div>

  </div>

</div>



<?= $this->endSection(); ?>