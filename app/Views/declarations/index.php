<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Note de détail
<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
Note de détail — dossier Nº<?= esc($folder["id"]) ?>
<?= $this->endSection(); ?>
<?= $this->section('add'); ?>
<a href="<?= base_url("dossiers/declaration/" . $folder["id"] . "/imprimer") ?>" target="_blank" class="btn btn-primary">Imprimer</a>
<a href="<?= base_url("dossiers/information/" . $folder["id"]) ?>" class="btn">Voir le dossier</a>
<?= $this->endSection(); ?>
<?= $this->section('cols'); ?>

<?php
$nb = static fn ($v): string => $v === null ? "-" : number_format((float) $v, 0, ",", " ");
$poidsColis = (float) ($folder["total_weight"] ?? 0);
$ecartPoids = $poidsColis > 0 ? $totaux["poids"] - $poidsColis : null;
?>

<div class="col-12">
  <div class="card mb-3">
    <div class="card-body">
      <div class="card-title">En-tête</div>
      <p class="text-muted">
        Destinataire, connaissement et colisage viennent du dossier. Seuls la provenance,
        le régime, l'agrément et le manifeste se saisissent ici.
      </p>

      <?= form_open("dossiers/declaration/entete") ?>
      <input type="hidden" name="folder_id" value="<?= esc($folder["id"]) ?>">
      <div class="row">
        <div class="col-md-3 mb-3">
          <label class="form-label" for="provenance">Provenance</label>
          <input type="text" name="provenance" id="provenance" class="form-control"
                 placeholder="Guinée" value="<?= esc($folder["provenance"] ?? "") ?>">
        </div>
        <div class="col-md-3 mb-3">
          <label class="form-label" for="manifest">Manifeste</label>
          <input type="text" name="manifest" id="manifest" class="form-control"
                 value="<?= esc($folder["manifest"] ?? "") ?>">
        </div>
        <div class="col-md-3 mb-3">
          <label class="form-label" for="customs_regime">Régime</label>
          <input type="text" name="customs_regime" id="customs_regime" class="form-control"
                 placeholder="C100" value="<?= esc($folder["customs_regime"] ?? "") ?>">
        </div>
        <div class="col-md-3 mb-3">
          <label class="form-label" for="agreement_number">Agrément</label>
          <input type="text" name="agreement_number" id="agreement_number" class="form-control"
                 value="<?= esc($folder["agreement_number"] ?? "") ?>">
        </div>
        <div class="col-md-3 mx-auto text-center">
          <button type="submit" class="btn w-100">Enregistrer l'en-tête</button>
        </div>
      </div>
      <?= form_close() ?>

      <hr>
      <div class="row text-center">
        <div class="col"><div class="text-muted small">Destinataire</div><strong><?= esc($folder["recipient"] ?: "-") ?></strong></div>
        <div class="col"><div class="text-muted small">Connaissement</div><strong><?= esc($folder["bl"] ?: "-") ?></strong></div>
        <div class="col"><div class="text-muted small">Colis</div><strong><?= esc($folder["items_count"] ?? 0) ?></strong></div>
        <div class="col"><div class="text-muted small">Poids des colis</div><strong><?= $nb($poidsColis) ?> kg</strong></div>
        <div class="col"><div class="text-muted small">Nº ART</div><strong><?= $totaux["lignes"] ?></strong></div>
      </div>
    </div>
  </div>

  <?php if ($totaux["lignes"] > 0) : ?>
    <?php if ($ecartPoids !== null and abs($ecartPoids) > 1) : ?>
      <div class="alert alert-warning" role="alert">
        <strong>Écart de poids.</strong>
        Les lignes totalisent <?= $nb($totaux["poids"]) ?> kg, les colis du dossier <?= $nb($poidsColis) ?> kg
        (<?= $ecartPoids > 0 ? "+" : "" ?><?= $nb($ecartPoids) ?> kg).
        Une déclaration dont les poids ne concordent pas se fait rejeter.
      </div>
    <?php endif ?>

    <?php if ($totaux["ecarts_caf"] !== []) : ?>
      <div class="alert alert-warning" role="alert">
        <strong>CAF différent de FOB + fret + assurance</strong> sur
        <?= count($totaux["ecarts_caf"]) ?> ligne(s) :
        <?php foreach ($totaux["ecarts_caf"] as $e) : ?>
          ART<?= esc($e["ligne"]) ?> déclaré <?= $nb($e["declare"]) ?> pour <?= $nb($e["calcule"]) ?> calculé.
        <?php endforeach ?>
        Simple signalement : c'est la valeur déclarée qui fait foi.
      </div>
    <?php endif ?>
  <?php endif ?>

  <div class="card mb-3">
    <div class="card-body">
      <div class="card-title">Lignes tarifaires <span class="badge bg-secondary"><?= $totaux["lignes"] ?></span></div>

      <?php if ($lignes === []) : ?>
        <div class="alert alert-info">Aucune ligne. Ajoutez le premier article ci-dessous.</div>
      <?php else : ?>
        <div class="table-responsive card-table">
          <table class="table table-vcenter table-sm">
            <thead>
              <tr>
                <th>ART</th>
                <th>Espèce tarifaire</th>
                <th>Nature</th>
                <th>Origine</th>
                <th class="text-end">Poids</th>
                <th class="text-end">FOB</th>
                <th class="text-end">Fret</th>
                <th class="text-end">Assurance</th>
                <th class="text-end">CAF</th>
                <th>TC/CH</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($lignes as $l) : ?>
                <tr>
                  <td><strong><?= esc($l["line_no"]) ?></strong></td>
                  <td><code><?= esc($l["hs_code"]) ?></code></td>
                  <td><?= esc($l["description"] ?: "-") ?></td>
                  <td><?= esc($l["origin"] ?: "-") ?></td>
                  <td class="text-end"><?= $nb($l["weight"]) ?></td>
                  <td class="text-end"><?= $nb($l["fob_value"]) ?></td>
                  <td class="text-end"><?= $nb($l["freight_value"]) ?></td>
                  <td class="text-end"><?= $nb($l["insurance_value"]) ?></td>
                  <td class="text-end"><strong><?= $nb($l["caf_value"]) ?></strong></td>
                  <td><?= esc($l["container_chassis"] ?: "-") ?></td>
                  <td class="text-end text-nowrap">
                    <a class="btn btn-sm <?= ($edition and $edition["id"] === $l["id"]) ? "btn-primary" : "" ?>"
                       href="<?= base_url("dossiers/declaration/" . $folder["id"] . "?ligne=" . $l["id"]) ?>#ligne">
                      Modifier
                    </a>
                    <?= form_open("dossiers/declaration/supprimer-ligne", ["class" => "d-inline"]) ?>
                    <input type="hidden" name="id" value="<?= esc($l["id"]) ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">Supprimer</button>
                    <?= form_close() ?>
                  </td>
                </tr>
              <?php endforeach ?>
            </tbody>
            <tfoot>
              <tr>
                <th colspan="4">Total</th>
                <th class="text-end"><?= $nb($totaux["poids"]) ?></th>
                <th class="text-end"><?= $nb($totaux["fob"]) ?></th>
                <th class="text-end"><?= $nb($totaux["fret"]) ?></th>
                <th class="text-end"><?= $nb($totaux["assurance"]) ?></th>
                <th class="text-end"><?= $nb($totaux["caf"]) ?></th>
                <th colspan="2"></th>
              </tr>
            </tfoot>
          </table>
        </div>
      <?php endif ?>
    </div>
  </div>

  <?php
  // En modification, les champs sont pré-remplis depuis la ligne; sinon on
  // repasse par set_value pour ne pas perdre la saisie après une erreur.
  $v = static function (string $champ, string $defaut = "") use ($edition) {
      return $edition !== null ? (string) ($edition[$champ] ?? "") : set_value($champ, $defaut);
  };
  ?>

  <div class="card <?= $edition ? "border-primary" : "" ?>" id="ligne">
    <div class="card-body">
      <div class="card-title d-flex justify-content-between align-items-center">
        <span><?= $edition ? "Modifier la ligne ART" . esc($edition["line_no"]) : "Ajouter une ligne" ?></span>
        <?php if ($edition) : ?>
          <a class="btn btn-sm" href="<?= base_url("dossiers/declaration/" . $folder["id"]) ?>">Annuler</a>
        <?php endif ?>
      </div>
      <?= form_open("dossiers/declaration/ligne") ?>
      <input type="hidden" name="folder_id" value="<?= esc($folder["id"]) ?>">
      <?php if ($edition) : ?>
        <input type="hidden" name="id" value="<?= esc($edition["id"]) ?>">
      <?php endif ?>
      <div class="row">
        <div class="col-md-1 mb-3">
          <label class="form-label" for="line_no">ART</label>
          <input type="number" min="1" name="line_no" id="line_no" class="form-control"
                 value="<?= $v("line_no", (string) $prochaine) ?>">
        </div>
        <div class="col-md-3 mb-3">
          <label class="form-label" for="hs_code">Espèce tarifaire*</label>
          <input required type="text" name="hs_code" id="hs_code" class="form-control"
                 placeholder="2009903000" value="<?= $v("hs_code") ?>">
          <small class="form-hint">Chiffres seuls, les séparateurs sont retirés.</small>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label" for="description">Nature</label>
          <input type="text" name="description" id="description" class="form-control"
                 placeholder="Jus multivitaminé" value="<?= $v("description") ?>">
        </div>
        <div class="col-md-2 mb-3">
          <label class="form-label" for="origin">Origine</label>
          <input type="text" name="origin" id="origin" class="form-control"
                 placeholder="GW" maxlength="10" value="<?= $v("origin") ?>">
        </div>
        <div class="col-md-2 mb-3">
          <label class="form-label" for="weight">Poids (kg)</label>
          <input type="text" name="weight" id="weight" class="form-control" value="<?= $v("weight") ?>">
        </div>

        <div class="col-md-2 mb-3">
          <label class="form-label" for="fob_value">Valeur FOB</label>
          <input type="text" name="fob_value" id="fob_value" class="form-control" value="<?= $v("fob_value") ?>">
        </div>
        <div class="col-md-2 mb-3">
          <label class="form-label" for="freight_value">Valeur fret</label>
          <input type="text" name="freight_value" id="freight_value" class="form-control" value="<?= $v("freight_value") ?>">
        </div>
        <div class="col-md-2 mb-3">
          <label class="form-label" for="insurance_value">Assurance</label>
          <input type="text" name="insurance_value" id="insurance_value" class="form-control" value="<?= $v("insurance_value") ?>">
          <small class="form-hint">Facultative.</small>
        </div>
        <div class="col-md-2 mb-3">
          <label class="form-label" for="caf_value">CAF</label>
          <input type="text" name="caf_value" id="caf_value" class="form-control" value="<?= $v("caf_value") ?>">
        </div>
        <div class="col-md-2 mb-3">
          <label class="form-label" for="complementary_quantity">Q. complémentaire</label>
          <input type="text" name="complementary_quantity" id="complementary_quantity" class="form-control" value="<?= $v("complementary_quantity") ?>">
        </div>
        <div class="col-md-2 mb-3">
          <label class="form-label" for="container_chassis">TC / CH</label>
          <input type="text" name="container_chassis" id="container_chassis" class="form-control" value="<?= $v("container_chassis") ?>">
        </div>
        <div class="col-12 mb-3">
          <label class="form-label" for="reference">Référence</label>
          <input type="text" name="reference" id="reference" class="form-control"
                 placeholder="324 0003 03 09" value="<?= $v("reference") ?>">
        </div>
        <div class="col-md-4 mx-auto text-center">
          <button type="submit" class="btn btn-primary w-100"><?= $edition ? "Enregistrer les modifications" : "Ajouter la ligne" ?></button>
        </div>
      </div>
      <?= form_close() ?>
    </div>
  </div>
</div>

<?= $this->endSection(); ?>
