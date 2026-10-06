<?= $this->extend('layouts/base'); ?>
<?= $this->section('title'); ?>
Connexion
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>


<div class="page page-center">
  <div class="container container-tight py-4">
    <div class="text-center mb-4">
      <a href="." class="navbar-brand navbar-brand-autodark"><img src="./static/logo.svg" height="36" alt=""></a>
    </div>
    <div class="card card-md">
      <div class="card-body">
        <h2 class="h2 text-center mb-4">Connectez vous à votre compte</h2>
        <?php if (session()->has("error")) : ?>
          <div class="alert alert-danger" role="alert">
            <?php if (is_string(session()->error)) : ?>
              <?= esc(session()->error) ?>
            <?php else : ?>
              <strong>403</strong> Accès refusé!
            <?php endif ?>
          </div>
        <?php endif ?>

        <?php if (session()->has("error_session")) : ?>
          <div class="alert alert-warning" role="alert">
            <strong>401</strong> Session introuvable ou expirée, veuillez vous reconnecter.
          </div>
        <?php endif ?>

        <?= form_open() ?>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" value="<?= set_value("email")  ?>" name="email" class="form-control" placeholder="votre@email.com">
        </div>
        <div class="mb-2">
          <label class="form-label">
            Mot de passe
          </label>
          <div class="input-group input-group-flat">
            <input type="password" name="password" value="<?= set_value("password") ?>" id="password" class="form-control" placeholder="votre mot de passe">
            <span class="input-group-text">
              <a href="#" id="togglePassword" class="link-secondary" title="Afficher/Cacher" data-bs-toggle="tooltip"><!-- Download SVG icon from http://tabler-icons.io/i/eye -->
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                  <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                  <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                  <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                </svg>
              </a>
            </span>
          </div>
        </div>
        <div class="form-footer">
          <button type="submit" class="btn btn-primary w-100">Se connecter</button>
        </div>

        <?= form_close() ?>
      </div>
    </div>
    <div class="text-center text-muted mt-3">
      ©2024 Trans It!
    </div>
  </div>
</div>
<script>
  document.getElementById("togglePassword").addEventListener("click", () => {
    const password = document.getElementById("password")
    const actualType = password.getAttribute("type")
    password.setAttribute("type", actualType == "password" ? "text" : "password");
  })
</script>


<?= $this->endSection(); ?>