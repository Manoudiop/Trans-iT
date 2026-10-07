<?= $this->extend('layouts/base'); ?>
<?= $this->section('title'); ?>
Créer une agence
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>

<div class="page page-center">
  <div class="container container-tight py-4">
    <div class="text-center mb-4">
      <h1 class="h2">Trans It!</h1>
      <p class="text-muted">Créez l'espace de votre agence de transit</p>
    </div>
    <div class="card card-md">
      <div class="card-body">

        <?php if (session()->has("error")) : ?>
          <div class="alert alert-danger" role="alert">
            <?= esc(session()->error) ?>
          </div>
        <?php endif ?>

        <?= form_open("inscription") ?>

        <h3 class="h4">Votre agence</h3>
        <div class="mb-3">
          <label class="form-label" for="name">Raison sociale*</label>
          <input required type="text" name="name" id="name" class="form-control"
                 value="<?= set_value("name") ?>" placeholder="Transit Dakar SARL">
        </div>
        <div class="mb-3">
          <label class="form-label" for="slug">Identifiant d'agence*</label>
          <div class="input-group">
            <input required type="text" name="slug" id="slug" class="form-control"
                   value="<?= set_value("slug") ?>" placeholder="transit-dakar"
                   pattern="[a-z0-9][a-z0-9-]{1,49}">
            <span class="input-group-text">.<?= esc(config(\Config\Tenancy::class)->baseDomain) ?></span>
          </div>
          <small class="form-hint">
            Minuscules, chiffres et tirets. C'est l'adresse par laquelle votre équipe se connectera.
          </small>
        </div>

        <h3 class="h4 mt-4">Compte administrateur</h3>
        <div class="mb-3">
          <label class="form-label" for="admin_name">Nom complet*</label>
          <input required type="text" name="admin_name" id="admin_name" class="form-control"
                 value="<?= set_value("admin_name") ?>" placeholder="John Ndiaye">
        </div>
        <div class="mb-3">
          <label class="form-label" for="admin_email">Email*</label>
          <input required type="email" name="admin_email" id="admin_email" class="form-control"
                 value="<?= set_value("admin_email") ?>" placeholder="j.ndiaye@exemple.com">
        </div>
        <div class="mb-3">
          <label class="form-label" for="admin_password">Mot de passe*</label>
          <input required minlength="8" type="password" name="admin_password" id="admin_password"
                 class="form-control" placeholder="8 caractères minimum">
        </div>
        <div class="mb-3">
          <label class="form-label" for="admin_password_confirm">Confirmation*</label>
          <input required minlength="8" type="password" name="admin_password_confirm"
                 id="admin_password_confirm" class="form-control" placeholder="Répétez le mot de passe">
        </div>

        <?php if (!empty($plan)) : ?>
          <div class="alert alert-info" role="alert">
            Vous démarrez sur l'offre <strong><?= esc($plan["name"]) ?></strong><?= $plan["price_cfa"] ? ", " . number_format((float) $plan["price_cfa"], 0, ",", " ") . " FCFA par mois" : ", gratuite" ?> :
            <?= $plan["max_users"] === null ? "utilisateurs illimités" : esc($plan["max_users"]) . " utilisateurs" ?>,
            <?= $plan["max_folders_per_month"] === null ? "dossiers illimités" : esc($plan["max_folders_per_month"]) . " dossiers par mois" ?>,
            <?= $plan["max_storage_mb"] === null ? "stockage illimité" : esc($plan["max_storage_mb"]) . " Mo de pièces jointes" ?>.
          </div>
        <?php endif ?>

        <div class="form-footer">
          <button type="submit" class="btn btn-primary w-100">Créer mon agence</button>
        </div>

        <?= form_close() ?>
      </div>
    </div>
    <div class="text-center text-muted mt-3">
      Vous avez déjà un compte ? Connectez-vous depuis l'adresse de votre agence.
    </div>
  </div>
</div>

<?= $this->endSection(); ?>
