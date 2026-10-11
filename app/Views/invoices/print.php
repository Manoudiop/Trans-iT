<?= $this->extend('layouts/no_js'); ?>
<?= $this->section('title'); ?>
Facture Dossier Nº <?= $id ?>
<?= $this->endSection(); ?>
<?= $this->section('content'); ?>

<style>
  .facture {
    max-width: 920px;
  }

  .facture .cadre {
    border: 1px solid #333;
  }

  .facture .cadre th,
  .facture .cadre td {
    border: 1px solid #333;
    padding: .3rem .5rem;
    vertical-align: top;
  }

  .facture .etiquette {
    font-size: .75rem;
    text-transform: uppercase;
    letter-spacing: .02em;
    color: #555;
  }

  .facture .repertoire th,
  .facture .repertoire td {
    padding: .25rem .5rem;
    border: 0;
    border-bottom: 1px solid #e3e3e3;
  }

  .facture .repertoire .section th {
    border-top: 1px solid #333;
    border-bottom: 1px solid #333;
    background: #f2f2f2;
    text-transform: uppercase;
    font-size: .8rem;
    letter-spacing: .03em;
  }

  .facture .repertoire .sous-total th {
    border-bottom: 1px solid #333;
  }

  /* Les montants s'alignent sur le chiffre, pas sur la largeur du glyphe. */
  .facture .montant {
    text-align: right;
    white-space: nowrap;
    font-variant-numeric: tabular-nums;
  }

  @media print {
    .btn {
      display: none;
    }

    body {
      font-size: 10px;
    }

    .facture {
      max-width: none;
    }

    /* Une ligne de poste coupée en deux par un saut de page est illisible. */
    .facture tr {
      page-break-inside: avoid;
    }

    .facture .total {
      page-break-inside: avoid;
    }
  }
</style>

<div class="container facture bg-white">

  <button type="button" onclick="window.print()" class="btn btn-primary mt-2">
    Imprimer
  </button>

  <?php
  // strtotime(null) vaut 0, donc une date vide s'imprimait « 01/01/1970 »
  // sur la facture remise au client.
  $leDate = static fn ($valeur): string => empty($valeur) ? "" : date("d/m/Y", strtotime($valeur));
  $fcfa = static fn ($montant): string => number_format((float) $montant, 2, ",", " ") . " FCFA";

  // Une référence suivie de sa date, sans « du » orphelin quand la date manque.
  $refEtDate = static function ($reference, $date) use ($leDate): string {
      $reference = trim((string) $reference);
      $date = $leDate($date);

      if ($reference === "" and $date === "") {
          return "-";
      }

      return esc($reference) . ($date === "" ? "" : " <span class=\"text-muted\">du</span> " . $date);
  };

  /**
   * Libellés des postes. Le classement, lui, vient de Config\Invoicing:
   * la facture et les états de gestion lisent ainsi le même découpage.
   */
  $libelles = [
      "duties_taxes" => "Droits et Taxes (déclaration jointe)",
      "agios" => "Agios 1/1000",
      "freight" => "Frêt",
      "bl_stamp" => "Timbre de connaissement",
      "shipping_taxe" => "Taxes de port",
      "boarding_disembarkation" => "Embarquement / Débarquement",
      "storing_guarding" => "Magasinage / Gardiennage",
      "container_transportation" => "Transport / Conteneur",
      "handling" => "Relevage sur parc / Engin de levage",
      "insurance" => "Assurance",
      "transportation" => "Transport",
      "expert_report" => "Rapport d'expertise",
      "customs_excort" => "Escorte Douane",
      "demurrage" => "Surestaries",
      "customs_clearance" => "Vacation Douane",
      "postal_package_withdrawal_fees" => "Frais de retraits de colis postaux",
      "customs_ts_visit" => "T.S. Douane + Visite",
      "full_land_rental" => "Location terre plein (PAD)",
      "visit_admissibility" => "Visite / Recevabilité",
      "indirect_fees" => "Taxes indirectes",
      "orbus_fees" => "DPI / Orbus",
      "trucking" => "Camionnage",
      "grouping" => "Mise en groupage",
      "commission_on_disbursements" => "Commission sur débours",
      "folder_opening_fees" => "Ouverture de dossier",
      "transit_commission" => "Commission transit",
      "customs_honorary_fees" => "Honoraires d'agréé en Douane",
      "had" => "H.A.D Ad Valorem",
      "internal_handling" => "Manutention",
      "loading_unloading" => "Empotage / Dépotage",
      "printer" => "Imprimés",
      "procedures_formalities" => "Démarches et formalités",
      "tps" => "T.P.S.",
  ];

  $sections = [
      "DÉBOURS" => $invoicing->debours,
      "INTERVENTIONS NON TAXABLES" => $invoicing->interventions,
      "INTERVENTIONS TAXABLES" => $invoicing->remuneration,
  ];
  ?>

  <div class="d-flex justify-content-between align-items-start pt-3 pb-2">
    <div>
      <?php // Le logo appartient à l'agence et se dépose dans ses paramètres.
      // Sans logo, aucune balise: une image cassée sur une facture remise au
      // client est pire que pas de logo du tout. ?>
      <?php if (agency_logo_url()) : ?>
        <img src="<?= agency_logo_url() ?>" style="max-height: 80px; max-width: 200px;" alt="<?= esc($agence["name"] ?? "") ?>">
      <?php endif ?>
    </div>
    <div class="text-end">
      <?php // Adresse, téléphone, NINEA et agrément se saisissent dans les
      // paramètres de l'agence: une ligne vide ne s'imprime pas. ?>
      <div class="h2 mb-1"><?= esc($agence["name"] ?? "") ?></div>
      <?php foreach ([
          "" => $agence["address"] ?? "",
          "Tél. " => $agence["phone"] ?? "",
          "NINEA " => $agence["ninea"] ?? "",
          "Agrément " => $agence["agreement_number"] ?? "",
      ] as $prefixe => $valeur) : ?>
        <?php if (!empty($valeur)) : ?>
          <div class="text-muted small"><?= esc($prefixe . $valeur) ?></div>
        <?php endif ?>
      <?php endforeach ?>
    </div>
  </div>

  <table class="cadre w-100 mb-3">
    <tr>
      <th class="text-center" style="background: #f2f2f2;" colspan="2">
        <span class="h3">FACTURE Nº <?= esc($reference) ?></span>
        <span class="ms-2"><?= $type == "EXP" ? "EXPORT" : "IMPORT" ?></span>
        <?php if (!empty($invoice_date)) : ?>
          <span class="ms-2 text-muted">du <?= $leDate($invoice_date) ?></span>
        <?php endif ?>
      </th>
    </tr>
    <tr>
      <td style="width: 45%;">
        <div class="etiquette">Doit</div>
        <div class="fw-bold"><?= esc($invoice_to["name"]) ?></div>
        <div class="small text-muted">Compte <?= esc($invoice_to["id"]) ?></div>
        <?php // Identifiants fiscaux du client: propres aux entreprises,
        // absents chez un particulier, donc affichés seulement s'ils sont là. ?>
        <?php if (!empty($invoice_to["ninea"])) : ?>
          <div class="small">NINEA <?= esc($invoice_to["ninea"]) ?></div>
        <?php endif ?>
        <?php if (!empty($invoice_to["ppm"])) : ?>
          <div class="small">PPM <?= esc($invoice_to["ppm"]) ?></div>
        <?php endif ?>
      </td>
      <td>
        <table class="w-100">
          <?php foreach ([
              "CNT / LTA" => $refEtDate($bl, $bl_of),
              "Vol / Navire" => $refEtDate($boat, $boat_of),
              "Déclaration" => esc($declaration ?: "-"),
              "Colis" => esc($items_count) . " colis — " . number_format((float) $total_weight, 0, ",", " ") . " kg",
          ] as $etiquette => $valeur) : ?>
            <tr>
              <td class="etiquette pe-2" style="border: 0; padding: .1rem 0; width: 7.5rem;"><?= $etiquette ?></td>
              <td style="border: 0; padding: .1rem 0;"><?= $valeur ?></td>
            </tr>
          <?php endforeach ?>
        </table>
      </td>
    </tr>
    <?php // Ligne entière masquée quand la désignation est vide: elle laissait
    // une bande blanche au milieu de l'en-tête. ?>
    <?php if (!empty($designation)) : ?>
      <tr>
        <td colspan="2">
          <div class="etiquette">Désignation</div>
          <?= esc($designation) ?>
        </td>
      </tr>
    <?php endif ?>
  </table>

  <table class="repertoire w-100 mb-3">
    <?php $total = 0; ?>
    <?php foreach ($sections as $titre => $postes) : ?>
      <?php
      // Section entièrement vide: inutile d'imprimer un intitulé suivi d'un
      // sous-total à zéro.
      $lignes = array_filter(
          $postes,
          static fn (string $poste): bool => (float) ($montants[$poste] ?? 0) !== 0.0
      );
      ?>
      <?php if ($lignes === []) : ?>
        <?php continue ?>
      <?php endif ?>
      <tr class="section">
        <th><?= esc($titre) ?></th>
        <th class="montant"></th>
      </tr>
      <?php $sousTotal = 0; ?>
      <?php foreach ($lignes as $poste) : ?>
        <?php $sousTotal += (float) $montants[$poste]; ?>
        <tr>
          <td><?= esc($libelles[$poste] ?? $poste) ?></td>
          <td class="montant"><?= $fcfa($montants[$poste]) ?></td>
        </tr>
      <?php endforeach ?>
      <?php $total += $sousTotal; ?>
      <tr class="sous-total">
        <th class="text-end">Sous-total <?= esc($titre) ?></th>
        <th class="montant"><?= $fcfa($sousTotal) ?></th>
      </tr>
    <?php endforeach ?>
  </table>

  <div class="d-flex justify-content-end mb-5 total">
    <table class="cadre" style="min-width: 22rem;">
      <tr>
        <td class="etiquette">Arrêtée la présente facture à la somme de</td>
      </tr>
      <tr>
        <td class="montant h2 mb-0"><?= $fcfa($invoice_amount) ?></td>
      </tr>
    </table>
  </div>

  <?php
  // invoice_amount est une colonne générée: la base somme elle-même les
  // trente-trois postes. Les lignes ci-dessus, elles, sont celles que
  // Config\Invoicing classe dans les trois sections. Un poste oublié dans
  // cette configuration disparaîtrait donc du détail tout en restant dans
  // le total: le client paierait une somme que sa facture ne justifie pas.
  // L'écart le révèle. Vérifié en retirant « tps » de la configuration.
  $ecart = round((float) $invoice_amount - $total, 2);
  ?>
  <?php if (abs($ecart) >= 0.01) : ?>
    <div class="alert alert-warning d-print-none">
      <strong>Ne remettez pas cette facture en l'état.</strong>
      Le détail ci-dessus totalise <?= $fcfa($total) ?>, soit
      <?= $fcfa(abs($ecart)) ?> de moins que le montant facturé: un poste
      chiffré n'apparaît sur aucune des trois sections. Il manque à son
      classement dans <code>Config\Invoicing</code>.
    </div>
  <?php endif ?>

</div>

<?= $this->endSection(); ?>
