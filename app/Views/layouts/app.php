<!doctype html>
<!--
* Tabler - Premium and Open Source dashboard template with responsive and high quality UI.
* @version 1.0.0-beta19
* @link https://tabler.io
* Copyright 2018-2023 The Tabler Authors
* Copyright 2018-2023 codecalm.net Paweł Kuna
* Licensed under MIT (https://github.com/tabler/tabler/blob/master/LICENSE)
-->
<html lang="fr">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <meta http-equiv="X-UA-Compatible" content="ie=edge" />
  <title><?= $this->renderSection("title"); ?> | Trans It!</title>
  <!-- CSS files -->
  <link rel="stylesheet" href="https://cdn.datatables.net/2.0.3/css/dataTables.bootstrap5.min.css">
  <script src="https://code.jquery.com/jquery-3.7.1.slim.min.js" integrity="sha256-kmHvs0B+OpCW5GVHUNjv9rOmY0IvSIRcf7zGUDTDQM8=" crossorigin="anonymous"></script>
  <link href="<?= base_url("pack/css/tabler.min.css?1684106062") ?>" rel="stylesheet" />
  <link href="<?= base_url("pack/css/tabler-flags.min.css?1684106062") ?>" rel="stylesheet" />
  <link href="<?= base_url("pack/css/tabler-payments.min.css?1684106062") ?>" rel="stylesheet" />
  <link href="<?= base_url("pack/css/tabler-vendors.min.css?1684106062") ?>" rel="stylesheet" />
  <link href="<?= base_url("pack/css/demo.min.css?1684106062") ?>" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
  <link rel="shortcut icon" href="<?= base_url("logo.png") ?>" type="image/x-icon">
  <style>
    @import url('https://rsms.me/inter/inter.css');

    :root {
      --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, San Francisco, Segoe UI, Roboto, Helvetica Neue, sans-serif;
    }

    body {
      font-feature-settings: "cv03", "cv04", "cv11";
    }

    i.ti {
      font-size: 18px;
    }

    #toastContainer {
      position: fixed;
      bottom: 20px;
      right: 20px;
      z-index: 100;
      width: fit-content;
      max-width: 500px;
    }
  </style>
</head>

<body>
  <script src="./dist/js/demo-theme.min.js?1684106062"></script>
  <div class="page">
    <!-- Navbar -->
    <header class="navbar navbar-expand-md d-print-none">
      <div class="container-xl">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>
        <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
          <a href="<?= base_url("tableau-de-bord") ?>">
            <img src="<?= base_url("logo.png") ?>" height="40" width="40" alt="Tabler" class="navbar-brand-image">
          </a>
        </h1>
        <div class="navbar-nav flex-row order-md-last">
          <div class="nav-item d-none d-md-flex me-3">
            <div class="btn-list">
              <a href="mailto:diopiboumanou@gmail.com" class="btn d-flex gap-2 align-items-center" target="_blank" rel="noreferrer">
                <i class="ti ti-headset"></i>
                <span>Support</span>
              </a>
            </div>
          </div>

          <div class="nav-item dropdown">
            <a href="#" class="nav-link d-flex lh-1 text-reset p-0 btn d-flex gap-2 align-items-center px-1" data-bs-toggle="dropdown" aria-label="Open user menu">
              <i class="ti ti-user"></i>
              <div class="text-start">
                <small class="text-primary"><?= esc(session()->userData["profile"]) ?></small> <br>
                <?= esc(session()->userData["name"]) ?>
              </div>
            </a>
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
              <a href="#" class="dropdown-item btn d-flex gap-2 align-items-center" data-bs-toggle="modal" data-bs-target="#modalIdResetPassword"><i class="ti ti-lock"></i> Modifier mon mot de passe</a>
              <a href="mailto:yankeesuprem@gmail.com" class="dropdown-item d-flex gap-2 align-items-center"><i class="ti ti-headset"></i> Support</a>
              <a href="<?= base_url("deconnexion") ?>" class="dropdown-item d-flex gap-2 align-items-center"><i class="ti ti-power"></i> Se déconnecter</a>
            </div>
          </div>
        </div>
      </div>
    </header>
    <header class="navbar-expand-md">
      <div class="collapse navbar-collapse" id="navbar-menu">
        <div class="navbar">
          <div class="container-xl">
            <ul class="navbar-nav">
              <?php if (session()->userData["profile"] == "ADMIN") : ?>
                <li class="nav-item <?= url_is("tableau-de-bord*") ? "active" : "" ?>">
                  <a class="nav-link d-flex align-items-center" href="<?= base_url("tableau-de-bord") ?>">
                    <span class="nav-link-icon d-md-none d-lg-inline-block"><!-- Download SVG icon from http://tabler-icons.io/i/home -->
                      <i class="ti ti-dashboard"></i>
                    </span>
                    <span class="nav-link-title">
                      Tableau de bord
                    </span>
                  </a>
                </li>
              <?php endif ?>
              <?php if (session()->userData["profile"] == "ADMIN") : ?>
                <li class="nav-item dropdown <?= url_is("utilisateurs*") ? "active" : "" ?>">
                  <a class="nav-link dropdown-toggle d-flex align-items-center" href="#navbar-help" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                      <i class="ti ti-users"></i>
                    </span>
                    <span class="nav-link-title">
                      Utilisateurs
                    </span>
                  </a>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" href="<?= base_url("utilisateurs/ajouter") ?>">
                      Créer un nouveau compte
                    </a>
                    <a class="dropdown-item" href="<?= base_url("utilisateurs") ?>">
                      Lister les utilisateurs
                    </a>
                  </div>
                </li>
              <?php endif ?>
              <?php if (session()->userData["profile"] != "OPERATEUR") : ?>
                <li class="nav-item dropdown <?= url_is("clients*") ? "active" : "" ?>">
                  <a class="nav-link dropdown-toggle d-flex align-items-center" href="#navbar-help" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                      <i class="ti ti-users"></i>
                    </span>
                    <span class="nav-link-title">
                      Clients
                    </span>
                  </a>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" href="<?= base_url("clients/ajouter") ?>">
                      Créer un nouveau compte
                    </a>
                    <a class="dropdown-item" href="<?= base_url("clients") ?>">
                      Lister les comptes
                    </a>
                  </div>
                </li>
              <?php endif ?>

              <li class="nav-item dropdown <?= url_is("dossiers*") ? "active" : "" ?>">
                <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                  <span class="nav-link-icon d-md-none d-lg-inline-block">
                    <i class="ti ti-folder"></i>
                  </span>
                  <span class="nav-link-title">
                    Dossiers
                  </span>
                </a>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="<?= base_url("dossiers/ajouter") ?>">
                    Créer un nouveau dossier
                  </a>
                  <a class="dropdown-item" href="<?= base_url("dossiers") ?>">
                    Lister les dossiers
                  </a>
                </div>
              </li>
              <?php if (session()->userData["profile"] != "OPERATEUR") : ?>
                <li class="nav-item dropdown <?= url_is("factures*") ? "active" : "" ?>">
                  <a class="nav-link dropdown-toggle d-flex align-items-center" href="#navbar-help" data-bs-toggle="dropdown" data-bs-auto-close="outside" role="button" aria-expanded="false">
                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                      <i class="ti ti-file"></i>
                    </span>
                    <span class="nav-link-title">
                      Factures
                    </span>
                  </a>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" href="<?= base_url("factures") ?>">
                      Lister les dossiers facturés
                    </a>
                    <a class="dropdown-item" href="<?= base_url("factures/non-factures") ?>">
                      Lister les dossiers non facturés
                    </a>
                  </div>
                </li>
              <?php endif ?>
              <li class="nav-item dropdown <?= url_is("rapports*") ? "active" : "" ?>">
                <a class="nav-link d-flex align-items-center" href="<?= base_url("rapports") ?>">
                  <span class="nav-link-icon d-md-none d-lg-inline-block"><!-- Download SVG icon from http://tabler-icons.io/i/home -->
                    <i class="ti ti-archive"></i>
                  </span>
                  <span class="nav-link-title">
                    Rapports
                  </span>
                </a>
              </li>
              <?php if (!empty(session()->userData["is_platform_admin"])) : ?>
                <li class="nav-item <?= url_is("console*") ? "active" : "" ?>">
                  <a class="nav-link d-flex align-items-center" href="<?= base_url("console") ?>">
                    <span class="nav-link-icon d-md-none d-lg-inline-block">
                      <i class="ti ti-building"></i>
                    </span>
                    <span class="nav-link-title">
                      Console
                    </span>
                  </a>
                </li>
              <?php endif ?>


            </ul>
            <div class="my-2 my-md-0 flex-grow-1 flex-md-grow-0 order-first order-md-last">
              <form action="<?= base_url("dossiers") ?>" class="d-flex gap-1">
                <div class="input-icon">
                  <span class="input-icon-addon">
                    <!-- Download SVG icon from http://tabler-icons.io/i/search -->
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                      <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                      <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0" />
                      <path d="M21 21l-6 -6" />
                    </svg>
                  </span>
                  <input type="text" name="r" value="<?= (url_is("dossiers*") and is_string($_GET["r"] ?? null)) ? esc($_GET["r"]) : '' ?>" class="form-control" placeholder="Numéro de dossier..." aria-label="Numéro de dossier">
                </div>
                <button type="submit" class="btn btn-primary text-center">
                  OK
                </button>

              </form>
            </div>
          </div>
        </div>
      </div>
    </header>
    <div class="page-wrapper">
      <!-- Page header -->
      <div class="page-header d-print-none">
        <div class="container-xl">
          <div class="row g-2 align-items-center">
            <?= $this->renderSection('breadcrumb'); ?>
            <div class="col-12">
              <!-- Page pre-title -->
              <div class="page-pretitle">
                Page
              </div>
              <h2 class="page-title">
                <?= $this->renderSection('h1'); ?>
              </h2>
            </div>
            <div class="col-auto ms-auto d-print-none">
              <?= $this->renderSection("add"); ?>
            </div>
          </div>
        </div>
      </div>
      <!-- Page body -->
      <div class="page-body">
        <div class="container-xl">
          <div class="row row-deck row-cards">
            <?= $this->renderSection('cols'); ?>
          </div>
        </div>
      </div>
      <footer class="footer footer-transparent d-print-none">
        <div class="container-xl">
          <div class="row text-center align-items-center flex-row-reverse">

            <div class="col-12 col-lg-auto mt-3 mt-lg-0">
              <ul class="list-inline list-inline-dots mb-0">
                <li class="list-inline-item">
                  &copy; 2024 Trans It!
                  Tout droit réservé.
                </li>
              </ul>
            </div>
          </div>
        </div>
      </footer>
    </div>
  </div>

  <div class="modal fade" id="modalIdResetPassword" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalTitleId">
            Modification de mot de passe
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <?= form_open("edit-password", ["id" => "editFormPwd"]) ?>
          <?= csrf_field() ?>

          <div class="mb-2">
            <label class="form-label">
              Mot de passe actuel
            </label>
            <div class="input-group input-group-flat">
              <input required type="password" name="password" value="<?= set_value("password") ?>" id="password" class="form-control" placeholder="votre mot de passe">
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

          <div class="mb-2">
            <label class="form-label">
              Nouveau mot de passe
            </label>
            <div class="input-group input-group-flat">
              <input minlength="7" required type="password" name="passwordn" value="<?= set_value("passwordn") ?>" id="passwordn" class="form-control" placeholder="Créez">
              <span class="input-group-text">
                <a href="#" id="togglePasswordn" class="link-secondary" title="Afficher/Cacher" data-bs-toggle="tooltip"><!-- Download SVG icon from http://tabler-icons.io/i/eye -->
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                    <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                  </svg>
                </a>
              </span>
            </div>
          </div>

          <div class="mb-2">
            <label class="form-label">
              Confirmer le mot de passe
            </label>
            <div class="input-group input-group-flat">
              <input required type="password" name="passwordc" value="<?= set_value("passwordc") ?>" id="passwordc" class="form-control" placeholder="Confirmez">
              <span class="input-group-text">
                <a href="#" id="togglePasswordc" class="link-secondary" title="Afficher/Cacher" data-bs-toggle="tooltip"><!-- Download SVG icon from http://tabler-icons.io/i/eye -->
                  <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                    <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                  </svg>
                </a>
              </span>
            </div>
          </div>

          <?= form_close() ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Fermer
          </button>
          <button type="submit" form="editFormPwd" class="btn btn-primary">Modifier le mot de passe</button>
        </div>
      </div>
    </div>
  </div>



  <div id="toastContainer">
    <?php if (session()->has("message")) : ?>
      <div class="w-100 alert alert-success alert-dismissible fade show" role="alert">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <strong>Succès!</strong> <?= esc(session()->message) ?>
      </div>
    <?php endif ?>

    <?php if (session()->has("error")) : ?>
      <div class="w-100 alert alert-danger alert-dismissible fade show" role="alert">
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <strong>Erreur!</strong> <?= esc(session()->error) ?>
      </div>
    <?php endif ?>

    <?php if (session()->has("new_password")) : ?>
      <div class="w-100 alert alert-warning" role="alert">
        <strong>Mot de passe provisoire:</strong> <code><?= esc(session()->new_password) ?></code><br>
        Notez-le maintenant, il ne sera plus affiché.
      </div>
    <?php endif ?>
  </div>

  <!-- Tabler Core -->
  <script src="<?= base_url("pack/js/tabler.min.js?1684106062") ?>" defer></script>
  <script src="<?= base_url("pack/js/demo.min.js?1684106062") ?>" defer></script>
  <script src="https://cdn.datatables.net/2.0.3/js/dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/2.0.3/js/dataTables.bootstrap5.min.js"></script>
  <script>
    document.getElementById("togglePassword").addEventListener("click", () => {
      const password = document.getElementById("password")
      const actualType = password.getAttribute("type")
      password.setAttribute("type", actualType == "password" ? "text" : "password");
    })
  </script>
  <script>
    document.getElementById("togglePasswordn").addEventListener("click", () => {
      const password = document.getElementById("passwordn")
      const actualType = password.getAttribute("type")
      password.setAttribute("type", actualType == "password" ? "text" : "password");
    })
  </script>
  <script>
    document.getElementById("togglePasswordc").addEventListener("click", () => {
      const password = document.getElementById("passwordc")
      const actualType = password.getAttribute("type")
      password.setAttribute("type", actualType == "password" ? "text" : "password");
    })
  </script>
  <?= $this->renderSection('js'); ?>
</body>

</html>