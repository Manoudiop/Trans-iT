<?= $this->extend('layouts/base'); ?>
<?= $this->section('title'); ?>
Nouveau mot de passe
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>

<div class="page page-center">
  <div class="container container-tight py-4">
    <div class="text-center mb-4">
      <span class="h2"><?= esc(agency()["name"] ?? "Trans It!") ?></span>
    </div>
    <div class="card card-md">
      <div class="card-body">
        <h2 class="h3 text-center mb-4">Choisissez un nouveau mot de passe</h2>

        <?php if (session()->has("error") and is_string(session()->error)) : ?>
          <div class="alert alert-danger" role="alert"><?= esc(session()->error) ?></div>
        <?php endif ?>

        <?= form_open("reinitialiser") ?>
        <?= csrf_field() ?>
        <?php // Le jeton voyage dans le formulaire: il identifie le compte,
        // qui n'est donc jamais demandé au visiteur. ?>
        <input type="hidden" name="jeton" value="<?= esc($jeton) ?>">

        <div class="mb-3">
          <label class="form-label" for="password">Nouveau mot de passe</label>
          <input required minlength="8" type="password" name="password" id="password"
                 class="form-control" placeholder="8 caractères minimum">
        </div>
        <div class="mb-3">
          <label class="form-label" for="password_confirm">Confirmation</label>
          <input required minlength="8" type="password" name="password_confirm" id="password_confirm"
                 class="form-control" placeholder="Répétez le mot de passe">
        </div>
        <div class="form-footer">
          <button type="submit" class="btn btn-primary w-100">Enregistrer</button>
        </div>
        <?= form_close() ?>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection(); ?>
