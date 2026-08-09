<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Informations dossier Nº<?= $id ?>
<?= $this->endSection(); ?>
<?= $this->section('breadcrumb'); ?>
<nav class="breadcrumb">
  <a class="breadcrumb-item" href="<?= base_url('factures') ?>">Factures</a>
  <a class="breadcrumb-item" href="<?= base_url('dossiers/information/' . $id) ?>#facture"><?= $id ?> <?= $type ?></a>
  <span class="breadcrumb-item active" aria-current="page">Supprimer la facture</span>
</nav>

<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
<span>
  Dossier <span class="text-primary">Nº <?= $id ?> <?= $type ?></span>
</span>
<?= $this->endSection(); ?>
<?= $this->section('cols'); ?>

<div class="col">
  <div class="card">
    <div class="card-body">
      <div class="card-title">Confimation de la suppression de facture</div>
      <div class="alert alert-warning" role="alert">
        <strong>Attention!</strong> Cette action est irréversible.
      </div>

      <p>Pour supprimer la facture du dossier, merci de saisir <code><?= $id ?></code> dans le champs ci-dessous:</p>
      <?= form_open(base_url("factures/supprimer/" . $id)) ?>
      <?= csrf_field() ?>
      <input type="text" name="id" class=" form-control" style="max-width: max-content;" placeholder="XXXXXXXXXX"> <br>
      <button type="submit" class="btn btn-danger">
        Supprimer la facture
      </button>

      <?= form_close() ?>
    </div>
  </div>
</div>



<?= $this->endSection(); ?>
<?= $this->section('js'); ?>
<script>
  let table = new DataTable('#merchTable');
</script>

<?= $this->endSection(); ?>