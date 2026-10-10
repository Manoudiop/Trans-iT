<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Rapprochement
<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
Décaissé contre facturé
<?= $this->endSection(); ?>
<?= $this->section('add'); ?>
<a href="<?= base_url("tresorerie") ?>" class="btn">Journal de trésorerie</a>
<?= $this->endSection(); ?>
<?= $this->section('cols'); ?>

<?php
$fcfa = static fn ($m): string => number_format((float) $m, 0, ",", " ");

$blocs = [
    "a_facturer" => [
        "titre" => "Décaissé mais jamais facturé",
        "couleur" => "danger",
        "explication" => "De l'argent est sorti pour ces dossiers sans qu'aucune facture n'existe. C'est une perte sèche tant que la facture n'est pas établie.",
        "colonne" => "Décaissé",
    ],
    "sous_facture" => [
        "titre" => "Sous-facturé",
        "couleur" => "warning",
        "explication" => "Vous avez décaissé plus que ce que vous avez refacturé en débours. Surestarie oubliée, magasinage sous-évalué, frais ajoutés après la facturation.",
        "colonne" => "Manque",
    ],
    "a_verifier" => [
        "titre" => "Facturé au-delà du décaissé",
        "couleur" => "secondary",
        "explication" => "Les débours facturés dépassent ce qui est enregistré en caisse. Soit une dépense n'a pas été saisie, soit le débours a été refacturé avec une marge — ce qui n'en est plus un.",
        "colonne" => "Écart",
    ],
];
?>

<div class="col-12">
  <div class="row row-cards mb-3">
    <?php foreach ($blocs as $cle => $bloc) : ?>
      <div class="col-md-4">
        <div class="card <?= $groupes[$cle] !== [] ? "border-" . $bloc["couleur"] : "" ?>">
          <div class="card-body text-center p-3">
            <div class="h1 m-0"><?= $fcfa($totaux[$cle]) ?></div>
            <div class="text-muted small">
              <?= esc($bloc["titre"]) ?><br>
              <span class="badge bg-<?= $bloc["couleur"] ?>"><?= count($groupes[$cle]) ?> dossier(s)</span>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach ?>
  </div>

  <div class="alert alert-info" role="alert">
    <strong><?= count($groupes["conformes"]) ?> dossier(s) conformes</strong>, au franc près.
    <?php if ($sansSaisie["dossiers"] > 0) : ?>
      <br>
      <?= $sansSaisie["dossiers"] ?> dossier(s) facturés n'ont <strong>aucun décaissement saisi</strong>
      (<?= $fcfa($sansSaisie["montant"]) ?> FCFA de débours facturés).
      Ils ne sont pas comptés comme des écarts: c'est de la donnée qui manque, pas une anomalie.
      Ils se résorberont au fur et à mesure que la caisse sera tenue.
    <?php endif ?>
  </div>

  <?php foreach ($blocs as $cle => $bloc) : ?>
    <?php if ($groupes[$cle] === []) : continue; endif ?>
    <div class="card mb-3">
      <div class="card-body">
        <div class="card-title d-flex justify-content-between align-items-center">
          <span>
            <?= esc($bloc["titre"]) ?>
            <span class="badge bg-<?= $bloc["couleur"] ?>"><?= count($groupes[$cle]) ?></span>
          </span>
          <strong class="text-<?= $bloc["couleur"] ?>"><?= $fcfa($totaux[$cle]) ?> FCFA</strong>
        </div>
        <p class="text-muted"><?= esc($bloc["explication"]) ?></p>

        <div class="table-responsive card-table">
          <table class="table table-vcenter">
            <thead>
              <tr>
                <th>Dossier</th>
                <th>Client</th>
                <th>Connaissement</th>
                <th class="text-end">Débours facturés</th>
                <th class="text-end">Décaissé</th>
                <th class="text-end"><?= esc($bloc["colonne"]) ?></th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($groupes[$cle] as $ligne) : ?>
                <?php
                $montant = $cle === "a_facturer"
                    ? (float) $ligne["decaisse"]
                    : abs((float) $ligne["ecart"]);
                ?>
                <tr>
                  <td>
                    <?= esc($ligne["id"]) ?>
                    <?php if (!$ligne["invoiced"]) : ?>
                      <span class="badge bg-danger">non facturé</span>
                    <?php endif ?>
                  </td>
                  <td><?= esc($ligne["invoice_to"]["name"] ?? "-") ?></td>
                  <td><?= esc($ligne["bl"]) ?></td>
                  <td class="text-end"><?= $fcfa($ligne["facture_debours"]) ?></td>
                  <td class="text-end"><?= $fcfa($ligne["decaisse"]) ?></td>
                  <td class="text-end">
                    <strong class="text-<?= $bloc["couleur"] ?>"><?= $fcfa($montant) ?></strong>
                  </td>
                  <td class="text-end">
                    <a class="btn btn-sm" target="_blank"
                       href="<?= base_url(($ligne["invoiced"] ? "factures/modifier/" : "factures/facturer/") . $ligne["id"]) ?>">
                      <?= $ligne["invoiced"] ? "Corriger" : "Facturer" ?>
                    </a>
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
