<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Modifier Utilisateurs
<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
Utilisateurs
<?= $this->endSection(); ?>
<?= $this->section('add'); ?>
<a href="<?= base_url("utilisateurs/ajouter") ?>" class="btn btn-success">
  <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
    <path d="M12 5l0 14" />
    <path d="M5 12l14 0" />
  </svg>
  Ajouter
</a>
<?= $this->endSection(); ?>
<?= $this->section('cols'); ?>


<div class="col">
  <div class="card">
    <div class="card-body">
      <?= form_open("utilisateurs/modifier") ?>
      <input type="text" value="<?= $id ?>" name="id" hidden>
      <?= csrf_field() ?>
      <div class="card-title">Modification de l'utilisateur</div>
      <div class="row">
        <div class="col-md col-lg-4">
          <div class="mb-3">
            <label for="name" class="form-label">Nom complet*</label>
            <input required type="text" class="form-control" name="name" id="name" value="<?= set_value("name", $name) ?>" placeholder="John Ndiaye" />
          </div>
        </div>
        <div class="col-md col-lg-4">
          <div class="mb-3">
            <label for="email" class="form-label">Email*</label>
            <input required type="text" class="form-control" name="email" id="email" value="<?= set_value("email", $email) ?>" placeholder="j.ndiaye@exemple.com" />
          </div>
        </div>
        <div class="col-md col-lg-4">
          <div class="mb-3">
            <label for="profile" class="form-label">profil*</label>
            <select required class="form-select" name="profile" id="profile">
              <option selected hidden value="">Sélectionnez un Profil</option>
              <option value="ADMIN" <?= set_select("profile", "ADMIN", $profile == "ADMIN") ?>>ADMIN</option>
              <option value="COMPTABLE" <?= set_select("profile", "COMPTABLE", $profile == "COMPTABLE") ?>>COMPTABLE</option>
              <option value="OPERATEUR" <?= set_select("profile", "OPERATEUR", $profile == "OPERATEUR") ?>>OPERATEUR</option>
            </select>
          </div>
        </div>
        <div class="col-md col-lg-4 mx-auto text-center">
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