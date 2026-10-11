<?= $this->extend('layouts/base'); ?>
<?= $this->section('title'); ?>
Mot de passe oublié
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>

<div class="page page-center">
  <div class="container container-tight py-4">
    <div class="text-center mb-4">
      <span class="h2"><?= esc(agency()["name"] ?? "Trans It!") ?></span>
    </div>
    <div class="card card-md">
      <div class="card-body">
        <h2 class="h3 text-center mb-2">Mot de passe oublié</h2>
        <p class="text-muted text-center mb-4">
          Indiquez l'adresse de votre compte. Vous recevrez un lien pour en
          choisir un nouveau.
        </p>

        <?php if (session()->has("message")) : ?>
          <div class="alert alert-success" role="alert"><?= esc(session()->message) ?></div>
        <?php endif ?>

        <?php if (session()->has("error") and is_string(session()->error)) : ?>
          <div class="alert alert-danger" role="alert"><?= esc(session()->error) ?></div>
        <?php endif ?>

        <?= form_open("mot-de-passe-oublie") ?>
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label" for="email">Email</label>
          <input required type="email" name="email" id="email" class="form-control"
                 value="<?= set_value("email") ?>" placeholder="votre@email.com">
        </div>
        <div class="form-footer">
          <button type="submit" class="btn btn-primary w-100">Envoyer le lien</button>
        </div>
        <?= form_close() ?>
      </div>
    </div>
    <div class="text-center text-muted mt-3">
      <a href="<?= base_url("/") ?>">Revenir à la connexion</a>
    </div>
  </div>
</div>

<?= $this->endSection(); ?>
