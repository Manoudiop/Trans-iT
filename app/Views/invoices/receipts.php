<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Encaissements
<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
Encaissements
<?= $this->endSection(); ?>
<?= $this->section('add'); ?>
<a href="<?= base_url("factures/encours") ?>" class="btn">Suivi des avances</a>
<?= $this->endSection(); ?>
<?= $this->section('cols'); ?>

<?php
$fcfa = static fn ($montant): string => number_format((float) $montant, 0, ",", " ");
$url = base_url("factures/encaissements");
?>

<div class="col-12 d-block">

  <div class="card mb-3">
    <div class="card-body">
      <?php // Filtres en GET: une période consultée doit pouvoir être
      // transmise par simple copie de l'adresse. ?>
      <form action="<?= esc($url, "attr") ?>" method="get" class="row g-2 align-items-end">
        <div class="col-sm-3">
          <label class="form-label" for="du">Du</label>
          <input type="date" class="form-control" name="du" id="du" value="<?= esc($du) ?>">
        </div>
        <div class="col-sm-3">
          <label class="form-label" for="au">Au</label>
          <input type="date" class="form-control" name="au" id="au" value="<?= esc($au) ?>">
        </div>
        <div class="col-sm-3">
          <label class="form-label" for="mode">Mode de règlement</label>
          <select class="form-select" name="mode" id="mode">
            <option value="">Tous</option>
            <?php foreach ($methodes as $cle => $libelle) : ?>
              <option value="<?= esc($cle, "attr") ?>" <?= $mode === $cle ? "selected" : "" ?>>
                <?= esc($libelle) ?>
              </option>
            <?php endforeach ?>
          </select>
        </div>
        <div class="col-sm-3">
          <button type="submit" class="btn btn-primary w-100">Afficher</button>
        </div>
      </form>
    </div>
  </div>

  <div class="row row-cards mb-3" id="ventilation">
    <div class="col-sm-6 col-lg-3">
      <div class="card">
        <div class="card-body text-center p-3">
          <div class="h1 m-0"><?= $fcfa($totaux["total"]) ?></div>
          <div class="text-muted small">
            encaissé sur la période (FCFA)<br>
            <?= esc($totaux["nombre"]) ?> règlement(s)
          </div>
        </div>
      </div>
    </div>

    <?php // Ventilation par mode: c'est elle qui permet de rapprocher la
    // caisse des espèces et le relevé bancaire des virements.
    //
    // On parcourt les modes réellement présents, et non la liste proposée à
    // la saisie: d'anciens règlements portent des modes qui n'y figurent
    // plus, et les ignorer donnerait une ventilation inférieure au total. ?>
    <?php foreach ($totaux["parMode"] as $cle => $ligne) : ?>
      <div class="col-sm-6 col-lg-3">
        <div class="card">
          <div class="card-body text-center p-3">
            <div class="h2 m-0"><?= $fcfa($ligne["montant"]) ?></div>
            <div class="text-muted small">
              <?= esc($methodes[$cle] ?? ($cle !== "" ? $cle : "Mode non renseigné")) ?><br>
              <?= esc($ligne["nombre"]) ?> règlement(s)
            </div>
          </div>
        </div>
      </div>
    <?php endforeach ?>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="card-title">
        Du <?= date("d/m/Y", strtotime($du)) ?> au <?= date("d/m/Y", strtotime($au)) ?>
        <?= $mode !== null ? " — " . esc($methodes[$mode]) : "" ?>
      </div>

      <?= view("partials/list_tools", [
          "options" => $options,
          "url" => $url,
          "placeholder" => "Référence, note ou nº de dossier",
      ]) ?>

      <div class="table-responsive card-table">
        <table class="table table-vcenter">
          <thead>
            <tr>
              <th><?= entete_triable("Date", "date", $options, $url) ?></th>
              <th><?= entete_triable("Dossier", "dossier", $options, $url) ?></th>
              <th>Client</th>
              <th class="text-end"><?= entete_triable("Montant", "montant", $options, $url) ?></th>
              <th><?= entete_triable("Mode", "mode", $options, $url) ?></th>
              <th>Référence</th>
              <th>Saisi par</th>
              <th>Observation</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($reglements === []) : ?>
              <tr>
                <td colspan="8" class="text-center text-muted py-4">
                  Aucun encaissement sur cette période.
                </td>
              </tr>
            <?php endif ?>
            <?php foreach ($reglements as $r) : ?>
              <tr>
                <td><?= $r["paid_at"] ? date("d/m/Y", strtotime($r["paid_at"])) : "-" ?></td>
                <td>
                  <a href="<?= base_url("factures/reglements/" . $r["folder_id"]) ?>">
                    <?= esc($r["folder_id"]) ?>
                  </a>
                </td>
                <td><?= esc($rattachements["clients"][(string) $r["folder_id"]] ?? "-") ?></td>
                <td class="text-end"><strong><?= $fcfa($r["amount"]) ?></strong></td>
                <td><?= esc($methodes[$r["method"]] ?? ($r["method"] ?: "-")) ?></td>
                <td><?= esc($r["reference"] ?: "-") ?></td>
                <td><?= esc($rattachements["auteurs"][$r["recorded_by"]] ?? "-") ?></td>
                <td><?= esc($r["note"] ?: "") ?></td>
              </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>

      <?= view("partials/pager", ["pager" => $pager]) ?>
    </div>
  </div>
</div>

<?= $this->endSection(); ?>
