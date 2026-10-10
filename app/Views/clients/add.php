<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Ajouter clients
<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
Clients
<?= $this->endSection(); ?>

<?= $this->section('cols'); ?>


<div class="col">
  <div class="card">
    <div class="card-body">
      <?= form_open("clients/ajouter") ?>
      <?= csrf_field() ?>
      <div class="card-title">Modification du client</div>
      <div class="row">
        <div class="col-md col-lg-4">
          <div class="mb-3">
            <label for="account_number" class="form-label">Numéro de compte*</label>
            <input minlength="5" required type="text" class="form-control text-uppercase" name="account_number" id="account_number" value="<?= set_value("account_number") ?>" placeholder="XXXXXXX" />
          </div>
        </div>
        <div class="col-md col-lg-4">
          <div class="mb-3">
            <label for="name" class="form-label">Nom complet*</label>
            <input required type="text" class="form-control" name="name" id="name" value="<?= set_value("name") ?>" placeholder="John Ndiaye" />
          </div>
        </div>
        <div class="col-md col-lg-4">
          <div class="mb-3">
            <label for="email" class="form-label">Email*</label>
            <input required type="email" class="form-control" name="email" id="email" value="<?= set_value("email") ?>" placeholder="j.ndiaye@exemple.com" />
          </div>
        </div>
        <div class="col-md col-lg-4">
          <div class="mb-3">
            <label for="phone" class="form-label">Téléphone*</label>
            <input required type="tel" class="form-control" name="phone" id="phone" value="<?= set_value("phone") ?>" placeholder="XXXXXXXX" />
          </div>
        </div>
        <?php // Identifiants d'entreprise: un client particulier n'en a pas. ?>
        <div class="col-md col-lg-4">
          <div class="mb-3">
            <label for="ninea" class="form-label">NINEA</label>
            <input type="text" class="form-control" name="ninea" id="ninea" value="<?= set_value("ninea") ?>" placeholder="facultatif" />
          </div>
        </div>
        <div class="col-md col-lg-4">
          <div class="mb-3">
            <label for="ppm" class="form-label">PPM</label>
            <input type="text" class="form-control" name="ppm" id="ppm" value="<?= set_value("ppm") ?>" placeholder="facultatif" />
          </div>
        </div>
        <div class="col-12 mx-auto text-center">
          <button type="submit" class="btn btn-primary">
            Enregistrer les modifications
          </button>
        </div>
      </div>
      <?= form_close() ?>
    </div>
  </div>
</div>











<?= $this->endSection(); ?>