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
      <img src="<?= base_url("logo.png") ?>" height="100px" width="100px" alt="Logo">
    </div>
    <div class=" flex-grow-1 text-center" style="max-width: 400px;">
      <div class="h1 mb-0">Nom de l'entreprise</div>
      <p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Explicabo, natus! A atque quidem quae non necessitatibus ipsa, perferendis </p>
      <div class="h1 mb-0"><?= $type == "EXP" ? "EXPORT" : "IMPORT" ?></div>
    </div>
  </div>

  <div class="row">
    <div class="col">
      <div class="mb-2"><small>Date d'ouverture</small><br /><strong><?= date("d/m/Y", strtotime($open_date)) ?></strong></div>
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
          <div class="mb-2"><small>CNT/LTA</small> <br> <strong><?= $bl ?></strong> du <strong><?= date("d/m/Y", strtotime($bl_of)) ?></strong></div>
        </div>
        <div class="col">
          <div class="mb-2"><small>Navire/Vol</small> <br> <strong><?= $boat ?></strong> du <strong><?= date("d/m/Y", strtotime($boat_of)) ?></strong></div>
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