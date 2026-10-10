<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Suivi d'exploitation
<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
Suivi d'exploitation
<?= $this->endSection(); ?>
<?= $this->section('add'); ?>
<a href="<?= base_url("dossiers") ?>" class="btn">Tous les dossiers</a>
<?= $this->endSection(); ?>
<?= $this->section('cols'); ?>

<?php
$total = 0;
$bloques = 0;

foreach ($parEtape as $etape => $dossiers) {
    $total += count($dossiers);
    foreach ($dossiers as $d) {
        if ($workflow->isBlocked($etape, $d["stage_days"] === null ? null : (int) $d["stage_days"])) {
            $bloques++;
        }
    }
}
?>

<div class="col-12 d-block">
  <div class="row row-cards mb-3">
    <?php foreach ($parEtape as $etape => $dossiers) : ?>
      <?php
      $enRetard = 0;
      foreach ($dossiers as $d) {
          if ($workflow->isBlocked($etape, $d["stage_days"] === null ? null : (int) $d["stage_days"])) {
              $enRetard++;
          }
      }
      $actif = $filtre === $etape;
      ?>
      <div class="col-6 col-lg-2">
        <a class="text-decoration-none" href="<?= base_url("dossiers/suivi?etape=" . urlencode($etape)) ?>">
          <div class="card <?= $actif ? "border-primary" : ($enRetard ? "border-danger" : "") ?>">
            <div class="card-body text-center p-2">
              <div class="h2 m-0 text-body"><?= count($dossiers) ?></div>
              <div class="text-muted" style="font-size:.7rem;line-height:1.1"><?= esc($workflow->label($etape)) ?></div>
              <?php if ($enRetard) : ?>
                <span class="badge bg-danger mt-1"><?= $enRetard ?> en retard</span>
              <?php endif ?>
            </div>
          </div>
        </a>
      </div>
    <?php endforeach ?>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="card-title d-flex justify-content-between align-items-center">
        <span>
          <?php if ($filtre === null) : ?>
            Dossiers au-delà du délai de leur étape
          <?php else : ?>
            <?= esc($workflow->label($filtre)) ?>
          <?php endif ?>
          <span class="badge bg-secondary"><?= count($lignes) ?></span>
        </span>
        <?php if ($filtre !== null) : ?>
          <a class="btn btn-sm" href="<?= base_url("dossiers/suivi") ?>">Voir seulement les retards</a>
        <?php endif ?>
      </div>

      <p class="text-muted">
        <?php if ($filtre === null) : ?>
          <?= $bloques ?> dossier(s) en retard sur <?= $total ?> en cours.
          Cliquez une étape ci-dessus pour voir tous ses dossiers.
        <?php else : ?>
          <?= esc($workflow->action($filtre)) ?>
          Alerte au-delà de <?= $workflow->threshold($filtre) ?> jour(s).
        <?php endif ?>
      </p>

      <?php if ($lignes === []) : ?>
        <div class="alert alert-success mb-0" role="alert">
          <?= $filtre === null ? "Aucun dossier en retard." : "Aucun dossier à cette étape." ?>
        </div>
      <?php else : ?>
        <div class="table-responsive card-table">
          <table id="myTable" class="table table-vcenter">
            <thead>
              <tr>
                <th>Nº</th>
                <th>Étape</th>
                <th>Client</th>
                <th>Connaissement</th>
                <th>Depuis</th>
                <th class="text-end">Jours</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($lignes as $folder) : ?>
                <?php $jours = $folder["stage_days"] === null ? null : (int) $folder["stage_days"]; ?>
                <tr class="<?= $folder["en_retard"] ? "table-danger" : "" ?>">
                  <td><?= esc($folder["id"]) ?> <?= esc($folder["type"]) ?></td>
                  <td><?= esc($workflow->label($folder["stage"])) ?></td>
                  <td><?= esc($folder["invoice_to"]["name"]) ?></td>
                  <td><?= esc($folder["bl"]) ?></td>
                  <td><?= $folder["stage_since"] ? date("d/m/Y", strtotime($folder["stage_since"])) : "non renseignée" ?></td>
                  <td class="text-end">
                    <?php if ($jours === null) : ?>
                      <span class="badge bg-warning">inconnu</span>
                    <?php else : ?>
                      <strong class="<?= $folder["en_retard"] ? "text-danger" : "" ?>"><?= $jours ?></strong>
                    <?php endif ?>
                  </td>
                  <td class="text-end">
                    <a href="<?= base_url("dossiers/modifier/" . $folder["id"]) ?>" class="btn btn-sm">Ouvrir</a>
                  </td>
                </tr>
              <?php endforeach ?>
            </tbody>
          </table>
        </div>
      <?php endif ?>
    </div>
  </div>
</div>

<?= $this->endSection(); ?>
