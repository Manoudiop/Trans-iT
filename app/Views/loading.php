<?= $this->extend('layouts/base'); ?>
<?= $this->section('title'); ?>
Chargements...
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>


<div class="page page-center">
  <div class="container container-tight py-4">
    <div class="card card-md">
      <div class="card-body">

        <div class="d-flex justify-content-center align-items-center">
          <div class="spinner-border text-primary spinner-border-lg" role="status">
          </div>
        </div>

        <h2 class="h2 text-center mb-4">Téléchargement du fichier sur serveur...</h2>
        <hr>
        <div><strong>Nom:</strong> <?= $name ?></div>
        <div class="mb-3"><strong>Taille du fichier:</strong> <?= $size ?> MB</div>
        <div class="text-center d-grid">
          <a class="btn btn-danger" href="<?= previous_url() ?>" role="button">Annuler</a>
        </div>
      </div>
    </div>
    <div class="text-center text-muted mt-3">
      ©2024 Trans It! - by <a href="https://linkedin.com/in/yankeesuprem">Yankee</a>
    </div>
  </div>
</div>


<?= $this->endSection(); ?>