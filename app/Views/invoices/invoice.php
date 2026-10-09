<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Facturation du dossier Nº<?= $id ?>
<?= $this->endSection(); ?>
<?= $this->section('breadcrumb'); ?>
<nav class="breadcrumb">
  <a class="breadcrumb-item" href="<?= base_url('factures/non-factures') ?>">Dossiers non facturés</a>
  <span class="breadcrumb-item active" aria-current="page"><?= $id ?> <?= $type ?></span>
</nav>

<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
<span>
  Dossier <span class="text-primary">Nº <?= $id ?> <?= $type ?></span>
</span>
<?= $this->endSection(); ?>
<?= $this->section('add'); ?>
<button type="button" class="btn btn-primary d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalId">
  <i class="ti ti-resize"></i> Afficher toutes les informations
</button>
<div class="modal fade" id="modalId" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitleId">
          Toutes les informations de la facture
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-start">
        <div class="row">
          <div class="col-12 mb-3">
            <div class="card">
              <div class="card-body">
                <div class="card-title">Informations générales</div>
                <div class="row">
                  <div class="col-md col-lg-4 col-xl-3">
                    <p>
                      État: <?= $closed ? '<span class="badge bg-success">FERMÉ</span>' : '<span class="badge bg-warning">EN COURS</span>' ?>
                    </p>
                    <p>
                      Date d'ouverture: <code><?= date("d/m/Y", strtotime($open_date)) ?></code>
                    </p>
                  </div>
                  <div class="col-md col-lg-4 col-xl-3">
                    <p>Agent traitant: <code><?= $handling_agent ?></code> </p>
                    <p>Répertoire Nº: <code><?= $repository ?></code> </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 mb-3">
            <div class="card">
              <div class="card-body">
                <div class="card-title">Informations d'ouverture</div>
                <div class="row">
                  <div class="col-md-6 col-lg-4 col-xl-3">
                    <p>Orbus Nº: <code><?= $orbus_number ?></code></p>
                    <p>CNT/LTA: <code><?= $bl ?></code> du <code><?= date("d/m/Y", strtotime($bl_of)) ?></code> </p>
                  </div>
                  <div class="col-md-6 col-lg-4 col-xl-3">
                    <p>Navire/Vol: <code><?= $boat ?></code> du <code><?= date("d/m/Y", strtotime($boat_of)) ?></code> </p>
                    <p>Manifeste: <code><?= $manifest ?></code> Art. <code><?= $article ?></code> </p>
                  </div>
                  <div class="col-md-6 col-lg-4 col-xl-3">
                    <p>Déclaration: <code><?= $declaration ?></code></p>
                    <p>Destinataire: <code><?= $recipient ?></code></p>
                  </div>
                  <div class="col-md-6 col-lg-4 col-xl-3">
                    <p>Adresse: <code><?= $recipient_address ?></code></p>
                    <p>À facturer à: <code><a target="_blank" href="<?= base_url("clients?r=" . $invoice_to["id"]) ?>"><?= $invoice_to["id"] ?> - <?= $invoice_to["name"] ?> <i class="ti ti-link"></i></a></code></p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 mb-3">
            <div class="card">
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <div class="card-title mb-0">Colis (Total: <?= count($items) ?>)</div>
                </div>
                <div class="table-responsive">
                  <table id="merchTable" class="table table-vcenter w-100">
                    <thead>
                      <tr>
                        <th>Marque</th>
                        <th>Quantité</th>
                        <th>Nature</th>
                        <th>Poids</th>
                        <th>Volume</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($items as $item) : ?>
                        <tr>
                          <td><?= $item["brand"] ?></td>
                          <td><?= $item["quantity"] ?></td>
                          <td><?= $item["nature"] ?></td>
                          <td><?= $item["weight"] ?></td>
                          <td><?= $item["volume"] ?></td>
                        </tr>
                      <?php endforeach ?>

                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div class="col-12 mb-3">
            <div class="card">
              <div class="card-body">
                <div class="card-title">Traitement en douane</div>
                <div class="row">
                  <div class="col-md-6 col-lg-4 col-xl-3">
                    <p>Admis douane: <code><?= !in_array($customs_admission_date, [null, "0000-00-00"]) ? date("d/m/Y", strtotime($customs_admission_date)) : "-" ?></code></p>
                    <p>Inspecteur traitant: <code><?= $customs_inspector ?></code></p>
                  </div>
                  <div class="col-md-6 col-lg-4 col-xl-3">
                    <p>Date BAE: <code><?= !in_array($bae_date, [null, "0000-00-00"]) ? date("d/m/Y", strtotime($bae_date)) : "-" ?></code></p>
                    <p>Date livraison: <code><?= !in_array($delivery_date, [null, "0000-00-00"]) ? date("d/m/Y", strtotime($delivery_date)) : "-" ?></code></p>
                  </div>
                  <div class="col">
                    <p>Reserve: <code><?= $reserve ?></code></p>
                    <p>Manquant: <code><?= $missing ?></code></p>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-12">
            <div class="card">
              <div class="card-body">
                <div class="card-title">Informations de fermeture</div>
                <div class="row">
                  <div class="col-md-6 col-lg-4 col-xl-3">
                    <p>OT Nº: <code><?= $transit_order ?></code> du <code><?= !in_array($transit_order_date, [null, "0000-00-00"]) ? date("d/m/Y", strtotime($transit_order_date)) : "-" ?></code> </p>
                    <p>Facture Nº: <code><?= $invoice ?></code> du <code><?= !in_array($transit_order_date, [null, "0000-00-00"]) ? date("d/m/Y", strtotime($date_date)) : "-" ?></code> </p>
                  </div>
                  <div class="col-md-6 col-lg-4 col-xl-3">
                    <p>Reçu Nº: <code><?= $receipt ?></code> du <code><?= !in_array($receipt_date, [null, "0000-00-00"]) ? date("d/m/Y", strtotime($transit_order_date)) : "-" ?></code> </p>
                    <p>Chéque Nº: <code><?= $check ?></code> du <code><?= !in_array($check_date, [null, "0000-00-00"]) ? date("d/m/Y", strtotime($date_date)) : "-" ?></code> </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          Fermer
        </button>
      </div>
    </div>
  </div>
</div>
<?= $this->endSection(); ?>
<?= $this->section('cols'); ?>

<div class="col-12">
  <div class="card">
    <div class="card-body">
      <div class="card-title">Formulaire de facturation</div>
      <?= form_open() ?>
      <h4 class="text-primary">Entête</h4>
      <div class="row">
        <div class="col-12">
          <div class="mb-3" style="max-width: 400px">
            <label for="reference" class="form-label">Nº de facture</label>
            <input required type="text" class="form-control" name="reference" id="reference" value="<?= set_value("reference", "") ?>" placeholder="Automatique" readonly />
          </div>
          <div class="alert alert-primary" role="alert" style="max-width: 400px">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="automation" checked />
              <label class="form-check-label" for="automation">Numérotation automatique</label>
            </div>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="colis" class="form-label">Nombre de colis</label>
            <input type="number" class="form-control" id="colis" value="<?= $items_count ?>" readonly />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="colis" class="form-label">Poids en Kilogrammes</label>
            <input type="number" class="form-control" id="colis" value="<?= $total_weight ?>" readonly />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="invoice_author_name" class="form-label">Auteur de la facturation</label>
            <input required type="text" class="form-control" id="invoice_author_name" value="<?= esc(session()->userData["name"]) ?>" readonly />
            <input required type="number" name="invoice_author" hidden value="<?= esc(session()->userData["id"]) ?>" readonly />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="invoice_date" class="form-label">Date de facturation</label>
            <input required type="date" class="form-control" name="invoice_date" id="invoice_date" value="<?= set_value("invoice_date") ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="designation" class="form-label">Désignation</label>
            <input type="text" class="form-control" name="designation" id="designation" value="<?= set_value("designation") ?>" required />
          </div>
        </div>
        <h4 class="text-primary">Répertoire</h4>
        <h5 class="text-primary">Débours</h5>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="duties_taxes" class="form-label">Droits et taxes(déclaration jointe)</label>
            <input type="number" class="form-control" name="duties_taxes" id="duties_taxes" value="<?= set_value("duties_taxes", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="agios" class="form-label">Agios 1/1000</label>
            <input type="number" class="form-control" name="agios" id="agios" value="<?= set_value("agios", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="freight" class="form-label">Frêt</label>
            <input type="number" class="form-control" name="freight" id="freight" value="<?= set_value("freight", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="bl_stamp" class="form-label">Timbre de connaissement</label>
            <input type="number" class="form-control" name="bl_stamp" id="bl_stamp" value="<?= set_value("bl_stamp", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="shipping_taxe" class="form-label">Taxe de port</label>
            <input type="number" class="form-control" name="shipping_taxe" id="shipping_taxe" value="<?= set_value("shipping_taxe", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="bl_stamp" class="form-label">Timbre de connaissement</label>
            <input type="number" class="form-control" name="bl_stamp" id="bl_stamp" value="<?= set_value("bl_stamp", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="shipping_taxe" class="form-label">Taxes de ports</label>
            <input type="number" class="form-control" name="shipping_taxe" id="shipping_taxe" value="<?= set_value("shipping_taxe", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="boarding_disembarkation" class="form-label">Taxes de ports</label>
            <input type="number" class="form-control" name="boarding_disembarkation" id="boarding_disembarkation" value="<?= set_value("boarding_disembarkation", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="storing_guarding" class="form-label">Magasinage/Gardiennage</label>
            <input type="number" class="form-control" name="storing_guarding" id="storing_guarding" value="<?= set_value("storing_guarding", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="container_transportation" class="form-label">Transport / Container</label>
            <input type="number" class="form-control" name="container_transportation" id="container_transportation" value="<?= set_value("container_transportation", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="handling" class="form-label">Relevage sur parc / Engin de relevage</label>
            <input type="number" class="form-control" name="handling" id="handling" value="<?= set_value("handling", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="insurance" class="form-label">Assurance</label>
            <input type="number" class="form-control" name="insurance" id="insurance" value="<?= set_value("insurance", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="transportation" class="form-label">Transport</label>
            <input type="number" class="form-control" name="transportation" id="transportation" value="<?= set_value("transportation", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="expert_report" class="form-label">Rapport d'expertise</label>
            <input type="number" class="form-control" name="expert_report" id="expert_report" value="<?= set_value("expert_report", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="customs_excort" class="form-label">Escorte en Douane</label>
            <input type="number" class="form-control" name="customs_excort" id="customs_excort" value="<?= set_value("customs_excort", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="demurrage" class="form-label">Surestaries</label>
            <input type="number" class="form-control" name="demurrage" id="demurrage" value="<?= set_value("demurrage", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="customs_clearance" class="form-label">Vacation Douane</label>
            <input type="number" class="form-control" name="customs_clearance" id="customs_clearance" value="<?= set_value("customs_clearance", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="postal_package_withdrawal_fees" class="form-label">Frais de retrait de colis postaux</label>
            <input type="number" class="form-control" name="postal_package_withdrawal_fees" id="postal_package_withdrawal_fees" value="<?= set_value("postal_package_withdrawal_fees", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="customs_ts_visit" class="form-label">T.S. Douane + Visite</label>
            <input type="number" class="form-control" name="customs_ts_visit" id="customs_ts_visit" value="<?= set_value("customs_ts_visit", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="full_land_rental" class="form-label">Location terre plein(PAD)</label>
            <input type="number" class="form-control" name="full_land_rental" id="full_land_rental" value="<?= set_value("full_land_rental", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="visit_admissibility" class="form-label">Visite / Recevabilité</label>
            <input type="number" class="form-control" name="visit_admissibility" id="visit_admissibility" value="<?= set_value("visit_admissibility", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="indirect_fees" class="form-label">Taxes Indirectes</label>
            <input type="number" class="form-control" name="indirect_fees" id="indirect_fees" value="<?= set_value("indirect_fees", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="orbus_fees" class="form-label">DPI / Orbus</label>
            <input type="number" class="form-control" name="orbus_fees" id="orbus_fees" value="<?= set_value("orbus_fees", 0) ?>" />
          </div>
        </div>
        <h5 class="text-primary">Interventions non taxables</h5>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="trucking" class="form-label">Camionnage</label>
            <input type="number" class="form-control" name="trucking" id="trucking" value="<?= set_value("trucking", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="grouping" class="form-label">Groupage</label>
            <input type="number" class="form-control" name="grouping" id="grouping" value="<?= set_value("grouping", 0) ?>" />
          </div>
        </div>
        <h5 class="text-primary">Interventions taxables</h5>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="commission_on_disbursements" class="form-label">Commission sur débours</label>
            <input type="number" class="form-control" name="commission_on_disbursements" id="commission_on_disbursements" value="<?= set_value("commission_on_disbursements", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="folder_opening_fees" class="form-label">Ouverture de dossier</label>
            <input type="number" class="form-control" name="folder_opening_fees" id="folder_opening_fees" value="<?= set_value("folder_opening_fees", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="transit_commission" class="form-label">Commission transit</label>
            <input type="number" class="form-control" name="transit_commission" id="transit_commission" value="<?= set_value("transit_commission", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="customs_honorary_fees" class="form-label">Honoraires agréé en Douane</label>
            <input type="number" class="form-control" name="customs_honorary_fees" id="customs_honorary_fees" value="<?= set_value("customs_honorary_fees", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="had" class="form-label">H.A.D Ad Valorem</label>
            <input type="number" class="form-control" name="had" id="had" value="<?= set_value("had", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="internal_handling" class="form-label">Manutension</label>
            <input type="number" class="form-control" name="internal_handling" id="internal_handling" value="<?= set_value("internal_handling", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="loading_unloading" class="form-label">Empotage / Dépotage</label>
            <input type="number" class="form-control" name="loading_unloading" id="loading_unloading" value="<?= set_value("loading_unloading", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="printer" class="form-label">Imprimés</label>
            <input type="number" class="form-control" name="printer" id="printer" value="<?= set_value("printer", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="procedures_formalities" class="form-label">Demarches et formalités</label>
            <input type="number" class="form-control" name="procedures_formalities" id="procedures_formalities" value="<?= set_value("procedures_formalities", 0) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="tps" class="form-label">T.P.S</label>
            <input type="number" class="form-control" name="tps" id="tps" value="<?= set_value("tps", 0) ?>" />
          </div>
        </div>
        <div class="col-12 text-center">
          <button type="submit" class="btn btn-primary">
            Facturer
          </button>
        </div>
      </div>
      <?= csrf_field() ?>
      <?= form_close() ?>
    </div>
  </div>
</div>


<?= $this->endSection(); ?>
<?= $this->section('js'); ?>
<script>
  let table = new DataTable('#merchTable');
</script>
<script>
  const myModal = new bootstrap.Modal(
    document.getElementById("modalId"),
    options,
  );
</script>
<script>
  const id = document.getElementById("id")
  document.getElementById("automation").addEventListener("change", e => {
    if (e.target.checked) {
      id.setAttribute("readonly", true);
      id.setAttribute("placeholder", "Automatique");
      id.value = "";
    } else {
      id.removeAttribute("readonly");
      id.setAttribute("placeholder", "XXXXXXXX");
      id.setAttribute("required", true);
    }
  })
</script>
<?= $this->endSection(); ?>