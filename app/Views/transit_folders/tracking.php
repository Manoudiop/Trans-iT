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

<div class="col-12">
  <div class="row row-cards mb-3">
    <?php foreach ($parEtape as $etape => $dossiers) : ?>
      <?php
      $enRetard = 0;
      foreach ($dossiers as $d) {
          if ($workflow->isBlocked($etape, $d["stage_days"] === null ? null : (int) $d["stage_days"])) {
              $enRetard++;
          }
      }
      ?>
      <div class="col-sm-6 col-lg-2">
        <div class="card <?= $enRetard ? "border-danger" : "" ?>">
          <div class="card-body text-center p-3">
            <div class="h1 m-0"><?= count($dossiers) ?></div>
            <div class="text-muted small"><?= esc($workflow->label($etape)) ?></div>
            <?php if ($enRetard) : ?>
              <span class="badge bg-danger mt-2"><?= $enRetard ?> en retard</span>
            <?php else : ?>
              <span class="badge bg-success mt-2">à jour</span>
            <?php endif ?>
          </div>
        </div>
      </div>
    <?php endforeach ?>
  </div>

  <div class="alert <?= $bloques ? "alert-danger" : "alert-success" ?>" role="alert">
    <strong><?= $total ?> dossier(s) en cours</strong>,
    dont <strong><?= $bloques ?></strong> au-delà du délai de leur étape.
  </div>

  <?php foreach ($parEtape as $etape => $dossiers) : ?>
    <?php if ($dossiers === []) : continue; endif ?>
    <div class="card mb-3">
      <div class="card-body">
        <div class="card-title d-flex justify-content-between align-items-center">
          <span><?= esc($workflow->label($etape)) ?> <span class="badge bg-secondary"><?= count($dossiers) ?></span></span>
          <small class="text-muted">Alerte au-delà de <?= $workflow->threshold($etape) ?> jour(s)</small>
        </div>
        <p class="text-muted"><?= esc($workflow->action($etape)) ?></p>

        <div class="table-responsive card-table">
          <table class="table table-vcenter">
            <thead>
              <tr>
                <th>Nº</th>
                <th>Client</th>
                <th>Connaissement</th>
                <th>Dans l'étape depuis</th>
                <th class="text-end">Jours</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($dossiers as $folder) : ?>
                <?php
                $jours = $folder["stage_days"] === null ? null : (int) $folder["stage_days"];
                $retard = $workflow->isBlocked($etape, $jours);
                ?>
                <tr class="<?= $retard ? "table-danger" : "" ?>">
                  <td><?= esc($folder["id"]) ?> <?= esc($folder["type"]) ?></td>
                  <td><?= esc($folder["invoice_to"]["name"]) ?></td>
                  <td><?= esc($folder["bl"]) ?></td>
                  <td><?= $folder["stage_since"] ? date("d/m/Y", strtotime($folder["stage_since"])) : "date non renseignée" ?></td>
                  <td class="text-end">
                    <?php if ($jours === null) : ?>
                      <span class="badge bg-warning">inconnu</span>
                    <?php else : ?>
                      <strong class="<?= $retard ? "text-danger" : "" ?>"><?= $jours ?></strong>
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
      </div>
    </div>
  <?php endforeach ?>
</div>

<?= $this->endSection(); ?>
