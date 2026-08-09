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
  <link href="<?= base_url("pack/css/tabler.min.css?1684106062") ?>" rel="stylesheet" />
  <link href="<?= base_url("pack/css/tabler-flags.min.css?1684106062") ?>" rel="stylesheet" />
  <link href="<?= base_url("pack/css/tabler-payments.min.css?1684106062") ?>" rel="stylesheet" />
  <link href="<?= base_url("pack/css/tabler-vendors.min.css?1684106062") ?>" rel="stylesheet" />
  <link href="<?= base_url("pack/css/demo.min.css?1684106062") ?>" rel="stylesheet" />
  <!-- CSS files -->
  <style>
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
      font-feature-settings: "cv03", "cv04", "cv11";
      background-color: white;
    }

    small {
      opacity: 0.7;
      font-size: small;
    }
  </style>
</head>

<body class=" d-flex flex-column">
  <?= $this->renderSection('content'); ?>
</body>

</html>