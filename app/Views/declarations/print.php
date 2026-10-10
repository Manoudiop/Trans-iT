<?= $this->extend('layouts/no_js'); ?>
<?= $this->section('title'); ?>
Note de détail Nº <?= $folder["id"] ?>
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>

<style>
  @media print {
    .btn { display: none; }
    @page { size: landscape; }
  }
  .note table { font-size: .8rem; }
  .note th, .note td { border: 1px solid #333 !important; padding: .25rem .4rem !important; }
  .note .libelle { background: #f4f4f4; font-weight: 600; white-space: nowrap; }
</style>

<div class="container bg-white note">

  <button type="button" onclick="window.print()" class="btn btn-primary mt-2">Imprimer</button>

  <h2 class="text-center mt-3 mb-4" style="text-decoration: underline">NOTE DE DÉTAIL</h2>

  <?php $nb = static fn ($v): string => $v === null ? "" : number_format((float) $v, 0, ",", " "); ?>

  <div class="row mb-3">
    <div class="col-6">
      <div>Date&nbsp;: <strong><?= $folder["open_date"] ? date("d/m/Y", strtotime($folder["open_date"])) : "" ?></strong></div>
      <div>Provenance&nbsp;: <strong><?= esc($folder["provenance"] ?? "") ?></strong></div>
      <div>Destinataire&nbsp;: <strong><?= esc($folder["recipient"] ?? "") ?></strong></div>
      <div>Manifeste&nbsp;: <strong><?= esc($folder["manifest"] ?? "") ?></strong></div>
      <div>Nº BL&nbsp;: <strong><?= esc($folder["bl"] ?? "") ?></strong></div>
      <div>Régime&nbsp;: <strong><?= esc($folder["customs_regime"] ?? "") ?></strong></div>
    </div>
    <div class="col-6">
      <div>Nº ART&nbsp;: <strong><?= count($lignes) ?></strong></div>
      <div>Nature&nbsp;: <strong><?= esc($lignes[0]["description"] ?? "") ?></strong></div>
      <div>Nbre colis&nbsp;: <strong><?= esc($folder["items_count"] ?? 0) ?></strong></div>
      <div>Poids&nbsp;: <strong><?= $nb($totaux["poids"]) ?> KGS</strong></div>
      <div>Nº déclaration&nbsp;: <strong><?= esc($folder["declaration"] ?? "") ?></strong></div>
    </div>
  </div>

  <?php
  // Le formulaire papier se lit en colonnes: une colonne par article, une
  // ligne par rubrique. On transpose pour que la feuille imprimée
  // corresponde à celle que le déclarant a sous les yeux.
  $rubriques = [
      ["Espèce Tarifaire", static fn ($l) => $l["hs_code"]],
      ["Nature", static fn ($l) => $l["description"]],
      ["Origine", static fn ($l) => $l["origin"]],
      // Un seul poids saisi, reporté dans les deux colonnes du formulaire.
      ["Poids Brut", static fn ($l) => $l["weight"], true],
      ["Poids Net", static fn ($l) => $l["weight"], true],
      ["Valeur Fob", static fn ($l) => $l["fob_value"], true],
      ["Valeur Fret", static fn ($l) => $l["freight_value"], true],
      ["Assurance", static fn ($l) => $l["insurance_value"], true],
      ["Caf", static fn ($l) => $l["caf_value"], true],
      ["Q. Complémentaire", static fn ($l) => $l["complementary_quantity"]],
      ["TC / CH", static fn ($l) => $l["container_chassis"]],
  ];
  // Quatre colonnes minimum, comme le formulaire imprimé.
  $colonnes = max(4, count($lignes));
  ?>

  <table class="table table-bordered">
    <thead>
      <tr>
        <th></th>
        <?php for ($i = 0; $i < $colonnes; $i++) : ?>
          <th class="text-center">ART<?= $i + 1 ?></th>
        <?php endfor ?>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($rubriques as $rubrique) : ?>
        <?php [$libelle, $valeur] = $rubrique; $numerique = $rubrique[2] ?? false; ?>
        <tr>
          <td class="libelle"><?= esc($libelle) ?></td>
          <?php for ($i = 0; $i < $colonnes; $i++) : ?>
            <td class="<?= $numerique ? "text-end" : "" ?>">
              <?php if (isset($lignes[$i])) : ?>
                <?= $numerique ? $nb($valeur($lignes[$i])) : esc((string) ($valeur($lignes[$i]) ?? "")) ?>
              <?php endif ?>
            </td>
          <?php endfor ?>
        </tr>
      <?php endforeach ?>
    </tbody>
  </table>

  <div class="row mt-3">
    <div class="col-6">
      <?php
      // Celui du dossier l'emporte: il n'est renseigné que pour un envoi
      // dédouané sous l'agrément d'un confrère.
      $agrement = $folder["agreement_number"] ?: ($agence["agreement_number"] ?? "");
      ?>
      <?php if (!empty($agrement)) : ?>
        <div>Agrément&nbsp;: <strong><?= esc($agrement) ?></strong></div>
      <?php endif ?>
      <?php
      $references = array_filter(array_column($lignes, "reference"));
      ?>
      <?php if ($references !== []) : ?>
        <div><?= esc(implode(" — ", $references)) ?></div>
      <?php endif ?>
    </div>
    <div class="col-6 text-end">
      <div>Total CAF&nbsp;: <strong><?= $nb($totaux["caf"]) ?></strong></div>
      <div>Total poids&nbsp;: <strong><?= $nb($totaux["poids"]) ?> KGS</strong></div>
    </div>
  </div>

</div>

<?= $this->endSection(); ?>
