<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Trésorerie
<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
Caisse et banque
<?= $this->endSection(); ?>
<?= $this->section('add'); ?>
<a href="<?= base_url("tresorerie/rapprochement") ?>" class="btn">Décaissé contre facturé</a>
<?= $this->endSection(); ?>
<?= $this->section('cols'); ?>

<?php $fcfa = static fn ($m): string => number_format((float) $m, 0, ",", " "); ?>

<?php
// d-block est indispensable: le gabarit enveloppe cette section dans
// « row-deck », qui applique display:flex à chaque colonne. Sans cette
// classe, les cartes empilées ici deviennent des colonnes côte à côte et la
// première s'écrase à zéro.
?>
<div class="col-12 d-block">
  <div class="row row-cards mb-3">
    <?php foreach ($comptes as $compte) : ?>
      <div class="col-sm-6 col-lg-3">
        <div class="card <?= $compte["balance"] < 0 ? "border-danger" : "" ?>">
          <div class="card-body text-center p-3">
            <div class="h1 m-0 <?= $compte["balance"] < 0 ? "text-danger" : "" ?>">
              <?= $fcfa($compte["balance"]) ?>
            </div>
            <div class="text-muted small">
              <?= esc($compte["name"]) ?> <span class="badge bg-secondary"><?= esc($compte["type"]) ?></span>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach ?>
  </div>

  <?php if ($detentions !== []) : ?>
    <div class="card mb-3">
      <div class="card-body">
        <div class="card-title">Argent détenu par les agents</div>
        <p class="text-muted">
          Avances reçues, moins les dépenses justifiées, moins le liquide rendu.
          Un montant négatif signifie que l'agent a dépensé plus qu'il n'a reçu.
        </p>
        <div class="table-responsive card-table">
          <table class="table table-vcenter">
            <thead>
              <tr>
                <th>Agent</th>
                <th class="text-end">Avances</th>
                <th class="text-end">Justifié</th>
                <th class="text-end">Rendu</th>
                <th class="text-end">Détient encore</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($detentions as $d) : ?>
                <tr class="<?= (float) $d["detenu"] < 0 ? "table-danger" : "" ?>">
                  <td><?= esc($d["agent_nom"]) ?></td>
                  <td class="text-end"><?= $fcfa($d["avances"]) ?></td>
                  <td class="text-end"><?= $fcfa($d["justifie"]) ?></td>
                  <td class="text-end"><?= $fcfa($d["rendu"]) ?></td>
                  <td class="text-end"><strong><?= $fcfa($d["detenu"]) ?></strong></td>
                </tr>
              <?php endforeach ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php endif ?>

  <div class="card mb-3">
    <div class="card-body">
      <div class="card-title">Enregistrer un mouvement</div>
      <?= form_open("tresorerie/mouvement") ?>
      <div class="row">
        <div class="col-md-3 mb-3">
          <label class="form-label" for="kind">Nature*</label>
          <select required name="kind" id="kind" class="form-select">
            <?php foreach ($config->kinds as $cle => $nature) : ?>
              <option value="<?= esc($cle) ?>" <?= set_select("kind", $cle, $cle === "depense") ?>>
                <?= esc($nature["label"]) ?>
              </option>
            <?php endforeach ?>
          </select>
        </div>
        <div class="col-md-3 mb-3">
          <label class="form-label" for="amount">Montant (FCFA)*</label>
          <input required type="number" step="1" min="1" name="amount" id="amount"
                 class="form-control" value="<?= set_value("amount") ?>">
        </div>
        <div class="col-md-3 mb-3">
          <label class="form-label" for="moved_at">Date*</label>
          <input required type="date" name="moved_at" id="moved_at" class="form-control"
                 value="<?= set_value("moved_at", date("Y-m-d")) ?>">
        </div>
        <div class="col-md-3 mb-3">
          <label class="form-label" for="account_id">Compte</label>
          <select name="account_id" id="account_id" class="form-select">
            <option value="">—</option>
            <?php foreach ($comptes as $compte) : ?>
              <option value="<?= esc($compte["id"]) ?>" <?= set_select("account_id", (string) $compte["id"]) ?>>
                <?= esc($compte["name"]) ?>
              </option>
            <?php endforeach ?>
          </select>
          <small class="form-hint">Inutile si un agent dépense ce qu'il détient.</small>
        </div>

        <div class="col-md-4 mb-3">
          <label class="form-label" for="category">Catégorie (dépense)</label>
          <select name="category" id="category" class="form-select">
            <option value="">—</option>
            <optgroup label="Frais de dossier (refacturés)">
              <?php foreach ($config->folderCategories() as $cle => $libelle) : ?>
                <option value="<?= esc($cle) ?>" <?= set_select("category", $cle) ?>><?= esc($libelle) ?></option>
              <?php endforeach ?>
            </optgroup>
            <optgroup label="Charges de la maison">
              <?php foreach ($config->overheadCategories() as $cle => $libelle) : ?>
                <option value="<?= esc($cle) ?>" <?= set_select("category", $cle) ?>><?= esc($libelle) ?></option>
              <?php endforeach ?>
            </optgroup>
          </select>
        </div>
        <div class="col-md-3 mb-3">
          <label class="form-label" for="folder_id">Nº de dossier</label>
          <input type="text" name="folder_id" id="folder_id" class="form-control"
                 placeholder="202610000001" value="<?= set_value("folder_id") ?>">
          <small class="form-hint">Obligatoire pour un frais de dossier.</small>
        </div>
        <div class="col-md-3 mb-3">
          <label class="form-label" for="agent_id">Agent</label>
          <select name="agent_id" id="agent_id" class="form-select">
            <option value="">—</option>
            <?php foreach ($agents as $agent) : ?>
              <option value="<?= esc($agent["id"]) ?>" <?= set_select("agent_id", (string) $agent["id"]) ?>>
                <?= esc($agent["name"]) ?>
              </option>
            <?php endforeach ?>
          </select>
        </div>
        <div class="col-md-2 mb-3">
          <label class="form-label" for="reference">Référence</label>
          <input type="text" name="reference" id="reference" class="form-control" value="<?= set_value("reference") ?>">
        </div>
        <div class="col-12 mb-3">
          <label class="form-label" for="note">Observation</label>
          <input type="text" name="note" id="note" class="form-control" value="<?= set_value("note") ?>">
        </div>
        <div class="col-md-4 mx-auto text-center">
          <button type="submit" class="btn btn-primary w-100">Enregistrer</button>
        </div>
      </div>
      <?= form_close() ?>

      <hr>
      <details>
        <summary class="text-muted">Ajouter un compte</summary>
        <?= form_open("tresorerie/compte") ?>
        <div class="row mt-3">
          <div class="col-md-4 mb-3">
            <label class="form-label" for="account_name">Nom*</label>
            <input required type="text" name="name" id="account_name" class="form-control" placeholder="Banque Atlantique">
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label" for="account_type">Type*</label>
            <select required name="type" id="account_type" class="form-select">
              <option value="caisse">Caisse</option>
              <option value="banque">Banque</option>
            </select>
          </div>
          <div class="col-md-3 mb-3">
            <label class="form-label" for="opening_balance">Solde d'ouverture</label>
            <input type="number" step="1" name="opening_balance" id="opening_balance" class="form-control" value="0">
          </div>
          <div class="col-md-2 mb-3 d-flex align-items-end">
            <button type="submit" class="btn w-100">Créer</button>
          </div>
        </div>
        <?= form_close() ?>
      </details>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="card-title">Journal — <?= count($journal) ?> dernier(s) mouvement(s)</div>

      <?php if ($journal === []) : ?>
        <div class="alert alert-info mb-0" role="alert">Aucun mouvement enregistré.</div>
      <?php else : ?>
        <div class="table-responsive card-table">
          <table class="table table-vcenter">
            <thead>
              <tr>
                <th>Date</th>
                <th>Nature</th>
                <th>Catégorie</th>
                <th>Dossier</th>
                <th>Compte</th>
                <th>Agent</th>
                <th class="text-end">Montant</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($journal as $m) : ?>
                <?php $signe = (int) ($config->kinds[$m["kind"]]["sign"] ?? 0); ?>
                <tr>
                  <td><?= $m["moved_at"] ? date("d/m/Y", strtotime($m["moved_at"])) : "-" ?></td>
                  <td><?= esc($config->kinds[$m["kind"]]["label"] ?? $m["kind"]) ?></td>
                  <td><?= $m["category"] ? esc($config->label($m["category"])) : "-" ?></td>
                  <td>
                    <?php if ($m["folder_id"]) : ?>
                      <a target="_blank" href="<?= base_url("dossiers/information/" . $m["folder_id"]) ?>"><?= esc($m["folder_id"]) ?></a>
                    <?php else : ?>
                      -
                    <?php endif ?>
                  </td>
                  <td><?= $m["account_id"] ? esc($noms["comptes"][$m["account_id"]] ?? "?") : "-" ?></td>
                  <td><?= $m["agent_id"] ? esc($noms["agents"][$m["agent_id"]] ?? "?") : "-" ?></td>
                  <td class="text-end <?= $signe < 0 ? "text-danger" : ($signe > 0 ? "text-success" : "text-muted") ?>">
                    <strong><?= $signe < 0 ? "−" : ($signe > 0 ? "+" : "") ?><?= $fcfa($m["amount"]) ?></strong>
                  </td>
                  <td class="text-end">
                    <?= form_open("tresorerie/supprimer") ?>
                    <input type="hidden" name="id" value="<?= esc($m["id"]) ?>">
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
