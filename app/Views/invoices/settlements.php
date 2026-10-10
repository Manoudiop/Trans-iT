<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Règlements
<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
Règlements du dossier Nº<?= esc($folder["id"]) ?>
<?= $this->endSection(); ?>
<?= $this->section('add'); ?>
<a href="<?= base_url("dossiers/information/" . $folder["id"]) ?>" class="btn">Voir le dossier</a>
<?= $this->endSection(); ?>
<?= $this->section('cols'); ?>

<?php
$fcfa = static fn ($m): string => number_format((float) $m, 0, ",", " ");
$facture = (float) $folder["invoice_amount"];
$regle = (float) $folder["paid_amount"];
$pourcent = $facture > 0 ? min(100, round($regle / $facture * 100)) : 0;
?>

<div class="col-12">
  <div class="row row-cards mb-3">
    <div class="col-sm-4">
      <div class="card">
        <div class="card-body text-center p-3">
          <div class="h1 m-0"><?= $fcfa($facture) ?></div>
          <div class="text-muted small">Facturé (FCFA)</div>
        </div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="card border-success">
        <div class="card-body text-center p-3">
          <div class="h1 m-0"><?= $fcfa($regle) ?></div>
          <div class="text-muted small">Déjà réglé</div>
        </div>
      </div>
    </div>
    <div class="col-sm-4">
      <div class="card <?= $solde > 0 ? "border-danger" : "border-success" ?>">
        <div class="card-body text-center p-3">
          <div class="h1 m-0"><?= $fcfa($solde) ?></div>
          <div class="text-muted small">Reste dû</div>
        </div>
      </div>
    </div>
  </div>

  <div class="progress mb-3">
    <div class="progress-bar <?= $solde > 0 ? "bg-warning" : "bg-success" ?>"
         style="width: <?= $pourcent ?>%"><?= $pourcent ?>%</div>
  </div>

  <?php if ($solde > 0) : ?>
    <div class="card mb-3">
      <div class="card-body">
        <div class="card-title">Enregistrer un règlement</div>
        <?= form_open("factures/reglements/ajouter") ?>
        <input type="hidden" name="folder_id" value="<?= esc($folder["id"]) ?>">
        <div class="row">
          <div class="col-md-3 mb-3">
            <label class="form-label" for="amount">Montant (FCFA)*</label>
            <input required type="number" step="1" min="1" max="<?= (int) ceil($solde) ?>"
                   name="amount" id="amount" class="form-control"
                   value="<?= set_value("amount", (string) (int) ceil($solde)) ?>">
            <small class="form-hint">Solde: <?= $fcfa($solde) ?></small>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label" for="paid_at">Date*</label>
            <input required type="date" name="paid_at" id="paid_at" class="form-control"
                   value="<?= set_value("paid_at", date("Y-m-d")) ?>">
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label" for="method">Mode</label>
            <select name="method" id="method" class="form-select">
              <?php foreach ($methodes as $cle => $libelle) : ?>
                <option value="<?= esc($cle) ?>" <?= set_select("method", $cle) ?>><?= esc($libelle) ?></option>
              <?php endforeach ?>
            </select>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label" for="reference">Référence</label>
            <input type="text" name="reference" id="reference" class="form-control"
                   placeholder="Nº chèque, bordereau..." value="<?= set_value("reference") ?>">
          </div>
          <div class="col-12 mb-3">
            <label class="form-label" for="note">Observation</label>
            <input type="text" name="note" id="note" class="form-control" value="<?= set_value("note") ?>">
          </div>
          <div class="col-md-4 mx-auto text-center">
            <button type="submit" class="btn btn-primary w-100">Enregistrer le règlement</button>
          </div>
        </div>
        <?= form_close() ?>
      </div>
    </div>
  <?php else : ?>
    <div class="alert alert-success" role="alert">
      <strong>Facture soldée.</strong> Le dossier peut être clôturé.
    </div>
  <?php endif ?>

  <div class="card">
    <div class="card-body">
      <div class="card-title"><?= count($payments) ?> règlement(s) enregistré(s)</div>

      <?php if ($payments === []) : ?>
        <div class="alert alert-info mb-0" role="alert">Aucun règlement pour cette facture.</div>
      <?php else : ?>
        <div class="table-responsive card-table">
          <table class="table table-vcenter">
            <thead>
              <tr>
                <th>Date</th>
                <th class="text-end">Montant</th>
                <th>Mode</th>
                <th>Référence</th>
                <th>Observation</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($payments as $p) : ?>
                <tr>
                  <td><?= $p["paid_at"] ? date("d/m/Y", strtotime($p["paid_at"])) : "-" ?></td>
                  <td class="text-end"><strong><?= $fcfa($p["amount"]) ?></strong></td>
                  <td><?= esc($methodes[$p["method"]] ?? $p["method"] ?? "-") ?></td>
                  <td><?= esc($p["reference"] ?? "-") ?></td>
                  <td><?= esc($p["note"] ?? "") ?></td>
                  <td class="text-end">
                    <?= form_open("factures/reglements/supprimer") ?>
                    <input type="hidden" name="id" value="<?= esc($p["id"]) ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                    <?= form_close() ?>
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
