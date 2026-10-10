<?= $this->extend('layouts/no_js'); ?>
<?= $this->section('title'); ?>
Dossier Nº <?= $id ?>
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>

<style>
  @media print {
    .btn {
      display: none;
    }
  }
</style>


<div class="container bg-white">

  <button type="button" onclick="window.print()" class="btn btn-primary mt-2">
    Imprimer
  </button>


  <!-- header -->
  <div class="d-flex justify-content-between align-items-center py-3">
    <div>
      <?php if (agency_logo_url()) : ?>
        <img src="<?= agency_logo_url() ?>" style="max-height: 100px; max-width: 200px;" alt="<?= esc(agency()["name"] ?? "") ?>">
      <?php endif ?>
    </div>
    <div class=" flex-grow-1 text-center" style="max-width: 400px;">
      <?php // Cette fiche portait, elle aussi, un nom d'entreprise et un
      // texte de remplissage écrits en dur. ?>
      <div class="h1 mb-0"><?= esc(agency()["name"] ?? "") ?></div>
      <?php if (!empty(agency()["address"])) : ?>
        <div class="text-muted"><?= esc(agency()["address"]) ?></div>
      <?php endif ?>
      <?php if (!empty(agency()["phone"])) : ?>
        <div class="text-muted">Tél. <?= esc(agency()["phone"]) ?></div>
      <?php endif ?>
      <div class="h1 mb-0 mt-2"><?= $type == "EXP" ? "EXPORT" : "IMPORT" ?></div>
    </div>
  </div>

  <?php
  // strtotime(null) vaut 0: une date absente s'imprimait « 01/01/1970 ».
  $leDate = static fn ($valeur): string => empty($valeur) ? "-" : date("d/m/Y", strtotime($valeur));
  ?>

  <div class="row">
    <div class="col">
      <div class="mb-2"><small>Date d'ouverture</small><br /><strong><?= $leDate($open_date) ?></strong></div>
      <div class="mb-2"><small>Agent Traitant</small><br /><strong><?= $handling_agent ?></strong></div>
    </div>
    <div class="col">
      <div class="mb-2"><small>Transit Nº</small> <br> <strong><?= $id ?> <?= $type ?></strong></div>
      <div class="mb-2"><small>Répertoire</small> <br> <strong><?= $repository ?></strong></div>
    </div>
    <hr class="mt-2" style="border: solid 2px black;opacity:1">
    <div class="col-12">
      <div class="row">
        <div class="col">
          <div class="mb-2"><small>Orbus Nº</small> <br> <strong><?= $orbus_number ?></strong></div>
          <div class="mb-2"><small>Expéditeur</small> <br> <strong><?= $expeditor ?></strong></div>
          <div class="mb-2"><small>CNT/LTA</small> <br> <strong><?= $bl ?></strong> du <strong><?= $leDate($bl_of) ?></strong></div>
        </div>
        <div class="col">
          <div class="mb-2"><small>Navire/Vol</small> <br> <strong><?= $boat ?></strong> du <strong><?= $leDate($boat_of) ?></strong></div>
          <div class="mb-2"><small>Manifeste</small> <br> <strong><?= $manifest ?></strong> Art <strong><?= $article ?></strong></div>
          <div class="mb-2"><small>Déclaration</small> <br> <strong><?= $declaration ?></strong></div>
        </div>
        <div class="col">
          <div class="mb-2"><small>Destinataire</small> <br> <strong><?= $recipient ?></strong></div>
          <div class="mb-2"><small>Adresse</small> <br> <strong><?= $recipient_address ?></strong></div>
          <div class="mb-2"><small>À facturer à</small> <br> <strong><?= $invoice_to["id"] ?></strong></div>
        </div>
      </div>
    </div>
    <hr class="mt-2" style="border: solid 2px black;opacity:1">
    <div class="col-12">
      <table id="merchTable" class="table table-vcenter table-sm">
        <thead>
          <tr>
            <th>Marque</th>
            <th>Quantité</th>
            <th>Nature</th>
            <th>Poids</th>
            <th>Volume</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $item) : ?>
            <tr>
              <td width="80%"><?= $item["brand"] ?></td>
              <td width="auto"><?= $item["quantity"] ?></td>
              <td width="30%"><?= $item["nature"] ?></td>
              <td width="auto"><?= $item["weight"] ?></td>
              <td width="auto"><?= $item["volume"] ?></td>
            </tr>
          <?php endforeach ?>

        </tbody>
      </table>
    </div>

    <hr class="mt-2" style="border: solid 2px black;opacity:1">
    <div class="col-12">
      <div class="row">
        <div class="col-4">
          <div class="mb-2"><small>O.T. Nº</small> <br> <strong><?= $transit_order ?></strong> du <strong><?= !in_array($transit_order_date, [null, "0000-00-00"]) ? date("d/m/Y", strtotime($transit_order_date)) : "-" ?></strong></div>
          <div class="mb-2"><small>Facture Nº</small> <br> <strong><?= $invoice ?></strong> du <strong><?= !in_array($invoice_date, [null, "0000-00-00"]) ? date("d/m/Y", strtotime($invoice_date)) : "-" ?></strong></div>
          <div class="mb-2"><small>Reçu Nº</small> <br> <strong><?= $receipt ?></strong> du <strong><?= !in_array($receipt_date, [null, "0000-00-00"]) ? date("d/m/Y", strtotime($receipt_date)) : "-" ?></strong></div>
          <div class="mb-2"><small>Chèque Nº</small> <br> <strong><?= $check ?></strong> du <strong><?= !in_array($check_date, [null, "0000-00-00"]) ? date("d/m/Y", strtotime($check_date)) : "-" ?></strong></div>
        </div>
        <div class="col-4">
          <div class="mb-2"><small>Admis douane</small> <br> <strong><?= !in_array($customs_admission_date, [null, "0000-00-00"]) ? date("d/m/Y", strtotime($customs_admission_date)) : "-" ?></strong></div>
          <div class="mb-2"><small>Inspecteur traitant</small> <br> <strong><?= $customs_inspector ?></strong></div>
          <div class="mb-2"><small>Date B.A.E.</small> <br> <strong><?= !in_array($bae_date, [null, "0000-00-00"]) ? date("d/m/Y", strtotime($bae_date)) : "-" ?></strong></div>
          <div class="mb-2"><small>Date de livraison</small> <br> <strong><?= !in_array($delivery_date, [null, "0000-00-00"]) ? date("d/m/Y", strtotime($delivery_date)) : "-" ?></strong></div>
        </div>
        <div class="col-4">
          <div class="mb-2"><small>Réserve</small> <br> <strong><?= $reserve ?></strong></div>
          <div class="mb-2"><small>Manquant</small> <br> <strong><?= $missing ?></strong></div>
        </div>
      </div>
    </div>
  </div>

</div>



<?= $this->endSection(); ?>