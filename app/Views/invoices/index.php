<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Factures
<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
Factures
<?= $this->endSection(); ?>
<?= $this->section('add'); ?>
<a href="<?= base_url("factures/non-factures") ?>" class="btn btn-success">
  <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
    <path d="M12 5l0 14" />
    <path d="M5 12l14 0" />
  </svg>
  Facturer
</a>
<?= $this->endSection(); ?>
<?= $this->section('cols'); ?>
<div class="col">
  <div class="card">
    <div class="card-body">
      <div class="card-title"><?= isset($_GET["r"]) ? "Résultat: " : "Liste des dossiers: " ?><?= count($invoices) ?> dossier(s)</div>
      <div class="table-responsive card-table">
        <table id="myTable" class="table table-vcenter">
          <thead>
            <tr>
              <th></th>
              <th>État</th>
              <th>Date facturation</th>
              <th>Montant en FCFA TTC</th>
              <th>Nº</th>
              <th>Clients</th>
              <th>Auteur de la facturation</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($invoices as $invoice) : ?>
              <tr>
                <td>
                  <div class="dropdown open">
                    <button class="btn dropdown-toggle" type="button" id="triggerId<?= $invoice["id"] ?>" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="ti ti-settings"></i>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="triggerId<?= $invoice["id"] ?>">
                      <?php if ($invoice["invoiced"]) : ?>
                        <a class="dropdown-item <?= $invoice["closed"] ? "disabled" : "" ?>" href="<?= base_url("factures/modifier/" . $invoice["id"]) ?>">Modifier la facture</a>
                      <?php else : ?>
                        <a class="dropdown-item <?= $invoice["closed"] ? "disabled" : "" ?>" href="<?= base_url("factures/facturer/" . $invoice["id"]) ?>">Facturer</a>
                      <?php endif ?>
                      <a class="dropdown-item" href="<?= base_url("dossiers/information/" . $invoice["id"]) ?>#facture">Consulter la facture</a>
                      <a class="dropdown-item" href="<?= base_url("factures/imprimer/" . $invoice["id"]) ?>">Imprimer la facture</a>
                      <a class="dropdown-item <?= $invoice["closed"] ? "disabled" : "" ?>" href="<?= base_url("factures/supprimer/" . $invoice["id"]) ?>">Supprimer la facture</a>
                    </div>
                  </div>
                </td>
                <td><?= $invoice["closed"] ? "<span class='badge bg-success'>TERMINÉ</span>" : "<span class='badge bg-warning'>EN COURS</span>" ?></td>
                <td><?= date("d/m/Y", strtotime($invoice["invoice_date"])) ?></td>
                <td><?= number_format($invoice["invoice_amount"], 2, ",", " ") ?></td>
                <td><?= $invoice["id"]  ?> <?= $invoice["type"] ?></td>
                <td><a target="_blank" href="<?= base_url("clients?r=" . $invoice["invoice_to"]["id"]) ?>"><?= $invoice["invoice_to"]["id"]  ?> <?= $invoice["invoice_to"]["name"]  ?><i class="ti ti-link"></i></a></td>
                <td><a href="mailto:<?= $invoice["invoice_author"]["email"] ?>"><?= $invoice["invoice_author"]["name"] ?><i class="ti ti-link"></i></a></td>
              </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>


<?= $this->endSection(); ?>
<?= $this->section('js'); ?>
<script>
  let table = new DataTable('#myTable', {
    order: [
      [2, 'desc']
    ]
  });
</script>

<?= $this->endSection(); ?>