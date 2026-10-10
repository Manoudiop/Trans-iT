<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Encours clients
<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
Suivi des avances
<?= $this->endSection(); ?>
<?= $this->section('add'); ?>
<a href="<?= base_url("factures") ?>" class="btn">Factures</a>
<?= $this->endSection(); ?>
<?= $this->section('cols'); ?>

<?php
$fcfa = static fn ($montant): string => number_format((float) $montant, 0, ",", " ");

$totalEncours = 0;
$totalDebours = 0;
$totalFactures = 0;
$totalRegle = 0;
$totalEntamees = 0;
$totalTranches = array_fill_keys(array_keys($invoicing->aging), 0);

foreach ($lignes as $ligne) {
    $totalEncours += (float) $ligne["encours"];
    $totalDebours += (float) $ligne["debours"];
    $totalFactures += (int) $ligne["factures"];
    $totalRegle += (float) ($ligne["deja_regle"] ?? 0);
    $totalEntamees += (int) ($ligne["factures_entamees"] ?? 0);
    foreach (array_keys($invoicing->aging) as $cle) {
        $totalTranches[$cle] += (float) ($ligne[$cle] ?? 0);
    }
}
?>

<div class="col-12 d-block">
  <div class="row row-cards mb-3">
    <div class="col-sm-6 col-lg-3">
      <div class="card">
        <div class="card-body text-center p-3">
          <div class="h1 m-0"><?= $fcfa($totalEncours) ?></div>
          <div class="text-muted small">Encours total (FCFA)</div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card border-warning">
        <div class="card-body text-center p-3">
          <div class="h1 m-0"><?= $fcfa($totalDebours) ?></div>
          <div class="text-muted small">dont débours avancés</div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card">
        <div class="card-body text-center p-3">
          <div class="h1 m-0"><?= $totalFactures ?></div>
          <div class="text-muted small">
            factures non soldées
            <?php if ($totalEntamees) : ?>
              <br><span class="text-warning"><?= $totalEntamees ?> avec acompte (<?= $fcfa($totalRegle) ?> reçus)</span>
            <?php endif ?>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card <?= $totalTranches["b90_plus"] > 0 ? "border-danger" : "" ?>">
        <div class="card-body text-center p-3">
          <div class="h1 m-0"><?= $fcfa($totalTranches["b90_plus"]) ?></div>
          <div class="text-muted small">à plus de 90 jours</div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="card-title"><?= count($lignes) ?> client(s) avec un encours</div>
      <p class="text-muted">
        Les débours sont la trésorerie réellement sortie pour le compte du client —
        droits et taxes, magasinage, surestaries, fret. Le reste est la rémunération
        de la maison. La nature de chaque poste se règle dans <code>Config\Invoicing</code>.
      </p>

      <?php if ($lignes === []) : ?>
        <div class="alert alert-success mb-0" role="alert">Aucun encours: toutes les factures sont encaissées.</div>
      <?php else : ?>
        <div class="table-responsive card-table">
          <table id="myTable" class="table table-vcenter">
            <thead>
              <tr>
                <th>Client</th>
                <th class="text-end">Factures</th>
                <th class="text-end">Déjà réglé</th>
                <th class="text-end">Reste dû</th>
                <th class="text-end">dont débours</th>
                <th>Plus ancienne</th>
                <th class="text-end">Jours</th>
                <?php foreach ($invoicing->aging as $tranche) : ?>
                  <th class="text-end"><?= esc($tranche["label"]) ?></th>
                <?php endforeach ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($lignes as $ligne) : ?>
                <?php $vieux = (int) $ligne["jours_max"] > 90; ?>
                <tr class="<?= $vieux ? "table-danger" : "" ?>">
                  <td>
                    <a target="_blank" href="<?= base_url("clients?r=" . urlencode((string) $ligne["client_id"])) ?>">
                      <?= esc($ligne["client_nom"]) ?>
                    </a>
                  </td>
                  <td class="text-end"><?= esc($ligne["factures"]) ?></td>
                  <td class="text-end"><?= (float) ($ligne["deja_regle"] ?? 0) ? $fcfa($ligne["deja_regle"]) : "-" ?></td>
                  <td class="text-end"><strong><?= $fcfa($ligne["encours"]) ?></strong></td>
                  <td class="text-end"><?= $fcfa($ligne["debours"]) ?></td>
                  <td><?= $ligne["plus_ancienne"] ? date("d/m/Y", strtotime($ligne["plus_ancienne"])) : "-" ?></td>
                  <td class="text-end">
                    <strong class="<?= $vieux ? "text-danger" : "" ?>"><?= esc($ligne["jours_max"]) ?></strong>
                  </td>
                  <?php foreach (array_keys($invoicing->aging) as $cle) : ?>
                    <td class="text-end"><?= (float) ($ligne[$cle] ?? 0) ? $fcfa($ligne[$cle]) : "-" ?></td>
                  <?php endforeach ?>
                </tr>
              <?php endforeach ?>
            </tbody>
            <tfoot>
              <tr>
                <th>Total</th>
                <th class="text-end"><?= $totalFactures ?></th>
                <th class="text-end"><?= $fcfa($totalRegle) ?></th>
                <th class="text-end"><?= $fcfa($totalEncours) ?></th>
                <th class="text-end"><?= $fcfa($totalDebours) ?></th>
                <th></th>
                <th></th>
                <?php foreach (array_keys($invoicing->aging) as $cle) : ?>
                  <th class="text-end"><?= $fcfa($totalTranches[$cle]) ?></th>
                <?php endforeach ?>
              </tr>
            </tfoot>
          </table>
        </div>
      <?php endif ?>
    </div>
  </div>
</div>

<?= $this->endSection(); ?>
