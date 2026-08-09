<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Rapports
<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
Rapports
<?= $this->endSection(); ?>
<?= $this->section('cols'); ?>
<div class="col-sm-6 col-lg-4 col-xl-3 d-flex">
  <a href="<?= base_url("rapports/dossiers") ?>" class="card card-sm flex-fill">
    <img style="height: 200px; width:100%; object-fit:cover; object-position:center" class="card-img-top" src="<?= base_url("/folder.jpg") ?>" alt="Title" />
    <div class="card-body">
      <h4 class="card-title">Rapport des dossiers de transits</h4>
    </div>
  </a>
</div>
<?php if (in_array(session()->userData["profile"], ["ADMIN", "COMPTABLE"])) : ?>
  <div class="col-sm-6 col-lg-4 col-xl-3 d-flex">
    <a href="<?= base_url("rapports/factures") ?>" class="card card-sm flex-fill">
      <img style="height: 200px; width:100%; object-fit:cover; object-position:center" class="card-img-top" src="<?= base_url("/invoice.jpg") ?>" alt="Title" />
      <div class="card-body">
        <h4 class="card-title">Rapport des factures</h4>
      </div>
    </a>
  </div>
<?php endif ?>
<?= $this->endSection(); ?>