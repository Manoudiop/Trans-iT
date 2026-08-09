<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Informations dossier Nº<?= $id ?>
<?= $this->endSection(); ?>
<?= $this->section('breadcrumb'); ?>
<nav class="breadcrumb">
  <a class="breadcrumb-item" href="<?= base_url('dossiers') ?>">Dossiers</a>
  <span class="breadcrumb-item active" aria-current="page"><?= $id ?> <?= $type ?></span>
</nav>

<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
<span>
  Dossier <span class="text-primary">Nº <?= $id ?> <?= $type ?></span>
</span>
<?= $this->endSection(); ?>
<?= $this->section('add'); ?>
<div class="d-flex flex-wrap gap-2">
  <?php if (!$invoiced and session()->userData["profile"] != "OPERATEUR") : ?>
    <a href="<?= base_url("factures/facturer/" . $id) ?>" class="d-flex align-items-center justify-content-center gap-1 btn btn-sm btn-success">
      <i class="ti ti-file"></i> Facturer
    </a>
  <?php endif ?>
  <a href="<?= base_url("dossiers/modifier/" . $id) ?>" class="d-flex align-items-center justify-content-center gap-1 btn btn-sm btn-warning">
    <i class="ti ti-edit"></i> Modifier
  </a>
  <a href="<?= base_url("dossiers/supprimer/" . $id) ?>" class="d-flex align-items-center <?= $closed ? "disabled" : "" ?> justify-content-center gap-1 btn btn-sm btn-danger">
    <i class="ti ti-trash"></i> Supprimer
  </a>
  <a href="<?= base_url("dossiers/imprimer/" . $id) ?>" target="_blank" type="button" class="d-flex align-items-center justify-content-center gap-1 btn btn-sm btn-primary">
    <i class="ti ti-printer"></i> Imprimer dossier
  </a>
  <?php if ($invoiced) : ?>
    <a href="<?= base_url("factures/imprimer/" . $id) ?>" target="_blank" type="button" class="d-flex align-items-center justify-content-center gap-1 btn btn-sm btn-primary">
      <i class="ti ti-printer"></i> Imprimer facture
    </a>
  <?php endif ?>
</div>
<?= $this->endSection(); ?>
<?= $this->section('cols'); ?>


<div class="col-12">
  <div class="card">
    <div class="card-body">
      <div class="card-title">Informations générales</div>
      <div class="row">
        <div class="col-md col-lg-4 col-xl-3">
          <p>
            État<br /> <?= $closed ? '<span class="badge bg-success">FERMÉ</span>' : '<span class="badge bg-warning">EN COURS</span>' ?>
          </p>
          <p>
            Date d'ouverture<br /> <code><?= date("d/m/Y", strtotime($open_date)) ?></code>
          </p>
        </div>
        <div class="col-md col-lg-4 col-xl-3">
          <p>Agent traitant<br /> <code><?= $handling_agent ?></code> </p>
          <p>Répertoire Nº<br /> <code><?= $repository ?></code> </p>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="col-12">
  <div class="card">
    <div class="card-body">
      <div class="card-title">Informations d'ouverture</div>
      <div class="row">
        <div class="col-md-6 col-lg-4 col-xl-3">
          <p>Orbus Nº<br /> <code><?= $orbus_number ?></code></p>
          <p>CNT/LTA<br /> <code><?= $bl ?></code> du <code><?= date("d/m/Y", strtotime($bl_of)) ?></code> </p>
        </div>
        <div class="col-md-6 col-lg-4 col-xl-3">
          <p>Navire/Vol<br /> <code><?= $boat ?></code> du <code><?= date("d/m/Y", strtotime($boat_of)) ?></code> </p>
          <p>Manifeste<br /> <code><?= $manifest ?></code> Art. <code><?= $article ?></code> </p>
        </div>
        <div class="col-md-6 col-lg-4 col-xl-3">
          <p>Déclaration<br /> <code><?= $declaration ?></code></p>
          <p>Destinataire<br /> <code><?= $recipient ?></code></p>
        </div>
        <div class="col-md-6 col-lg-4 col-xl-3">
          <p>Adresse<br /> <code><?= $recipient_address ?></code></p>
          <p>À facturer à<br /> <code><a target="_blank" href="<?= base_url("clients?r=" . $invoice_to["id"]) ?>"><?= $invoice_to["id"] ?> - <?= $invoice_to["name"] ?> <i class="ti ti-link"></i></a></code></p>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="col-12">
  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="card-title mb-0">Colis</div>
      </div>
      <div class="table-responsive">
        <table id="merchTable" class="table table-vcenter">
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
                <td><?= $item["brand"] ?></td>
                <td><?= $item["quantity"] ?></td>
                <td><?= $item["nature"] ?></td>
                <td><?= $item["weight"] ?></td>
                <td><?= $item["volume"] ?></td>
              </tr>
            <?php endforeach ?>

          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="col-12">
  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="card-title mb-0">Fichiers joints (Total: <?= count($files) ?>)</div>
        <div class="card-title mb-0">
        </div>
      </div>
      <div class="table-responsive">
        <table id="merchTable" class="table table-vcenter">
          <thead>
            <tr>
              <th></th>
              <th>Nom du fichier</th>
              <th>Date de d'ajout</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($files as $file) : ?>
              <tr>
                <td>
                  <div class="dropdown">
                    <button class="btn dropdown-toggle" type="button" id="triggerId<?= $file["id"] ?>" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="ti ti-settings"></i>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="triggerId<?= $file["id"] ?>">
                      <a class="dropdown-item" href="<?= $file["url"] ?>">Afficher</a>
                      <a class="dropdown-item" download href="<?= $file["url"] ?>">Télécharger</a>
                    </div>
                  </div>
                </td>
                <td><?= $file["name"] ?></td>
                <td><?= date("d/m/Y H:i:s", strtotime($file["created_at"])) ?></td>
              </tr>
            <?php endforeach ?>

          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="col-12">
  <div class="card">
    <div class="card-body">
      <div class="card-title">Traitement en douane</div>
      <div class="row">
        <div class="col-md-6 col-lg-4 col-xl-3">
          <p>Admis douane<br /> <code><?= !in_array($customs_admission_date, [null, "0000-00-00"]) ? date("d/m/Y", strtotime($customs_admission_date)) : "-" ?></code></p>
          <p>Inspecteur traitant<br /> <code><?= $customs_inspector ?></code></p>
        </div>
        <div class="col-md-6 col-lg-4 col-xl-3">
          <p>Date BAE<br /> <code><?= !in_array($bae_date, [null, "0000-00-00"]) ? date("d/m/Y", strtotime($bae_date)) : "-" ?></code></p>
          <p>Date livraison<br /> <code><?= !in_array($delivery_date, [null, "0000-00-00"]) ? date("d/m/Y", strtotime($delivery_date)) : "-" ?></code></p>
        </div>
        <div class="col">
          <p>Reserve<br /> <code><?= $reserve ?></code></p>
          <p>Manquant<br /> <code><?= $missing ?></code></p>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if ($invoiced) : ?>
  <div id="#facture" class="col-12">
    <div class="card">
      <div class="card-body">
        <div class="card-title">Informations de facturation</div>
        <div class="row">
          <div class="col-md-6 col-lg-4 col-xl-3">
            <p>Montant total en FCFA<br /> <code><?= number_format($invoice_amount, 2, ",", " ") ?></code></p>
            <p>Référence<br /> <code><?= $reference ?></code></p>
          </div>
          <div class="col-md-6 col-lg-4 col-xl-3">
            <p>Date de facturation:<code><?= !in_array($invoice_date, [null, "0000-00-00"]) ? date("d/m/Y", strtotime($invoice_date)) : "-" ?></code></p>
            <p>Auteur de la facturation<br /> <code><a href="mailto:<?= $invoice_author["email"] ?>"> <?= $invoice_author["name"] ?><i class="ti ti-link"></i></a></code></p>
          </div>
          <?php if (session()->userData["profile"] != "OPERATEUR") : ?>
            <div class="col-12 d-flex justify-content-end flex-wrap gap-1">
              <a href="<?= base_url('factures/modifier/' . $id) ?>" type="button" class="btn <?= $closed ? "disabled" : "" ?> btn-sm btn-warning">
                <i class="ti ti-edit"></i> Modifier
              </a>
              <a href="<?= base_url('factures/supprimer/' . $id) ?>" type="button" class="btn <?= $closed ? "disabled" : "" ?> btn-sm btn-danger">
                <i class="ti ti-trash"></i> Supprimer
              </a>
              <a href="<?= base_url('factures/imprimer/' . $id) ?>" type="button" class="btn btn-sm btn-primary">
                <i class="ti ti-printer"></i> Imprimer
              </a>
            </div>
          <?php endif ?>
        </div>
      </div>
    </div>
  </div>
<?php endif ?>

<div class="col-12">
  <div class="card">
    <div class="card-body">
      <div class="card-title">Informations de fermeture</div>
      <div class="row">
        <div class="col-md-6 col-lg-4 col-xl-3">
          <p>OT Nº<br /> <code><?= $transit_order ?></code> du <code><?= !in_array($transit_order_date, [null, "0000-00-00"]) ? date("d/m/Y", strtotime($transit_order_date)) : "-" ?></code> </p>
          <p>Facture Nº<br /> <code><?= $invoice ?></code> du <code><?= !in_array($transit_order_date, [null, "0000-00-00"]) ? date("d/m/Y", strtotime($date_date)) : "-" ?></code> </p>
        </div>
        <div class="col-md-6 col-lg-4 col-xl-3">
          <p>Reçu Nº<br /> <code><?= $receipt ?></code> du <code><?= !in_array($receipt_date, [null, "0000-00-00"]) ? date("d/m/Y", strtotime($receipt_date)) : "-" ?></code> </p>
          <p>Chéque Nº<br /> <code><?= $check ?></code> du <code><?= !in_array($check_date, [null, "0000-00-00"]) ? date("d/m/Y", strtotime($check_date)) : "-" ?></code> </p>
        </div>
      </div>
    </div>
  </div>
</div>






<?= $this->endSection(); ?>
<?= $this->section('js'); ?>
<script>
  let table = new DataTable('#merchTable');
</script>

<?= $this->endSection(); ?>