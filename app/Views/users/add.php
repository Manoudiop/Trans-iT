<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Ajouter Utilisateurs
<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
Utilisateurs
<?= $this->endSection(); ?>
<?= $this->section('cols'); ?>


<div class="col">
  <div class="card">
    <div class="card-body">
      <?= form_open("utilisateurs/modifier") ?>
      <?= csrf_field() ?>
      <div class="card-title">Ajout d'utilisateur</div>
      <div class="row">
        <div class="col-md col-lg-4">
          <div class="mb-3">
            <label for="name" class="form-label">Nom complet*</label>
            <input required type="text" class="form-control" name="name" id="name" value="<?= set_value("name", "") ?>" placeholder="John Ndiaye" />
          </div>
        </div>
        <div class="col-md col-lg-4">
          <div class="mb-3">
            <label for="email" class="form-label">Email*</label>
            <input required type="text" class="form-control" name="email" id="email" value="<?= set_value("email", "") ?>" placeholder="j.ndiaye@exemple.com" />
          </div>
        </div>
        <div class="col-md col-lg-4">
          <div class="mb-3">
            <label for="profile" class="form-label">profil*</label>
            <select required class="form-select" name="profile" id="profile">
              <option selected hidden value="">Sélectionnez un Profil</option>
              <option value="ADMIN" <?= set_select("profile", "ADMIN") ?>>ADMIN</option>
              <option value="COMPTABLE" <?= set_select("profile", "COMPTABLE") ?>>COMPTABLE</option>
              <option value="OPERATEUR" <?= set_select("profile", "OPERATEUR") ?>>OPERATEUR</option>
            </select>
          </div>
        </div>
        <div class="col-12">
          <div class="alert alert-primary" role="alert">
            <strong>Information!</strong> Un mot de passe provisoire est généré automatiquement à la création
            et affiché une seule fois. Transmettez-le à l'utilisateur, qui devra le changer à sa première connexion.
          </div>

        </div>
        <div class="col-md col-lg-4 mx-auto text-center">
          <button type="submit" class="btn btn-primary">
            Créer le compte
          </button>
        </div>
      </div>
      <?= form_close() ?>
    </div>
  </div>
</div>











<?= $this->endSection(); ?>