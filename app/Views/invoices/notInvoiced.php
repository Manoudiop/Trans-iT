<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Factures
<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
Factures - Dossiers non facturés
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
      <?php $url = base_url("factures/non-factures"); ?>
      <div class="card-title">
        <?= $options->search !== "" ? "Résultat de la recherche" : "Dossiers restant à facturer" ?>
      </div>

      <?= view("partials/list_tools", [
          "options" => $options,
          "url" => $url,
          "placeholder" => "Numéro de dossier ou connaissement",
      ]) ?>

      <div class="table-responsive card-table">
        <table class="table table-vcenter">
          <thead>
            <tr>
              <th></th>
              <th><?= entete_triable("Date d'ouverture", "date", $options, $url) ?></th>
              <th><?= entete_triable("État", "etat", $options, $url) ?></th>
              <th>Facturé ?</th>
              <th><?= entete_triable("Nº", "numero", $options, $url) ?></th>
              <th>Clients</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($invoices === []) : ?>
              <tr>
                <td colspan="6" class="text-center text-muted py-4">
                  <?= $options->search !== ""
                      ? "Aucun dossier ne correspond à « " . esc($options->search) . " »."
                      : "Tous les dossiers sont facturés." ?>
                </td>
              </tr>
            <?php endif ?>
            <?php foreach ($invoices as $invoice) : ?>
              <tr>
                <td>
                  <div class="dropdown open">
                    <button class="btn dropdown-toggle" type="button" id="triggerId<?= $invoice["id"] ?>" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="ti ti-settings"></i>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="triggerId<?= $invoice["id"] ?>">
                      <a class="dropdown-item" href="<?= base_url("factures/facturer/" . $invoice["id"]) ?>">Facturer</a>
                      <a class="dropdown-item" target="_blank" href="<?= base_url("factures/information/" . $invoice["id"]) ?>">Consulter le dossier</a>
                    </div>
                  </div>
                </td>
                <td><?= $invoice["open_date"] ? date("d/m/Y", strtotime($invoice["open_date"])) : "-" ?></td>
                <td><?= $invoice["closed"] ? "<span class='badge bg-success'>TERMINÉ</span>" : "<span class='badge bg-warning'>EN COURS</span>" ?></td>
                <td><?= $invoice["invoiced"] ? "<span class='badge bg-success'>OUI</span>" : "<span class='badge bg-danger'>NON</span>" ?></td>
                <td><?= $invoice["id"]  ?> <?= $invoice["type"] ?></td>
                <td><a target="_blank" href="<?= base_url("clients?r=" . $invoice["invoice_to"]["id"]) ?>"><?= $invoice["invoice_to"]["id"]  ?> <?= $invoice["invoice_to"]["name"]  ?><i class="ti ti-link"></i></a></td>
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
<?= $this->section('js'); ?>

<?= $this->endSection(); ?>