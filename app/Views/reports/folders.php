<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Rapports des dossiers de transit
<?= $this->endSection(); ?>
<?= $this->section('breadcrumb'); ?>
<nav class="breadcrumb">
  <a class="breadcrumb-item" href="<?= base_url('rapports') ?>">Rapports</a>
  <span class="breadcrumb-item active" aria-current="page">Rapports des dossiers de transit</span>
</nav>

<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
<span>
  Rapports <span class="text-primary">Dossiers de transit</span>
</span>
<?= $this->endSection(); ?>
<?= $this->section('cols'); ?>


<div class="col-12">
  <div class="card">
    <div class="card-body">
      <div class="card-title">Générer un rapports</div>
      <form action="#result" class="row">

        <div class="col-sm-6 col-lg-4 col-xl-3">
          <div class="mb-3">
            <label for="from" class="form-label">Date de début</label>
            <input type="date" class="form-control" value="<?= esc(is_string($_GET["from"] ?? null) ? $_GET["from"] : date("Y-m-01")) ?>" name="from" id="from" required />
          </div>
        </div>

        <div class="col-sm-6 col-lg-4 col-xl-3">
          <div class="mb-3">
            <label for="to" class="form-label">Date de fin</label>
            <input type="date" class="form-control" value="<?= esc(is_string($_GET["to"] ?? null) ? $_GET["to"] : date("Y-m-d")) ?>" name="to" id="to" required />
          </div>
        </div>
        <div class="col-sm-6 col-lg-4 col-xl-3">
          <div class="mb-3">
            <label for="type" class="form-label">Type de dossier</label>
            <select class="form-select" name="type" id="type">
              <option value="all" selected>Tous</option>
              <option value="IMP">IMPORT</option>
              <option value="EXP">EXPORT</option>
            </select>
          </div>
        </div>
        <div class="col-sm-6 col-lg-4 col-xl-3">
          <div class="mb-3">
            <label for="invoice_to" class="form-label">Client</label>
            <select class="form-select" name="invoice_to" id="invoice_to">
              <option value="all" selected>Tous</option>
              <?php foreach ($clients as $client) : ?>
                <option value="<?= $client["id"] ?>"><?= $client["id"] ?> - <?= $client["name"] ?></option>
              <?php endforeach ?>
            </select>
          </div>
        </div>
        <div class="col-12 text-center">
          <button class="btn btn-primary" type="submit">Générer</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div id="result" class="col-12">
  <div class="card">
    <div class="card-body">
      <div class="card-title">Résultats</div>
      <div class="table-responsive card-table">
        <table id="myTable" class="table table-vcenter table-sm">
          <thead>
            <tr>
              <th></th>
              <th>Date d'ouverture</th>
              <th>Etats</th>
              <th>Faturé?</th>
              <th>Nº</th>
              <th>Clients</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($folders as $folder) : ?>
              <tr>
                <td>
                  <div class="dropdown open">
                    <button class="btn dropdown-toggle" type="button" id="triggerId<?= $folder["id"] ?>" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="ti ti-settings"></i>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="triggerId<?= $folder["id"] ?>">
                      <?php if (!$folder["invoiced"] and session()->userData["profile"] != "OPERATEUR") : ?>
                        <a class="dropdown-item" href="<?= base_url("factures/facturer/" . $folder["id"]) ?>">Facturer</a>
                      <?php endif ?>
                      <a class="dropdown-item" target="_blank" href="<?= base_url("dossiers/information/" . $folder["id"]) ?>">Consulter</a>
                      <a class="dropdown-item" target="_blank" href="<?= base_url("dossiers/imprimer/" . $folder["id"]) ?>">Imprimer</a>
                      <a class="dropdown-item  <?= $folder["closed"] ? "disabled" : "" ?>" href="<?= base_url("dossiers/supprimer/" . $folder["id"]) ?>">Supprimer</a>
                    </div>
                  </div>
                </td>
                <td><?= date("d/m/Y", strtotime($folder["open_date"])) ?></td>
                <td><?= $folder["closed"] ? "<span class='badge bg-success'>TERMINÉ</span>" : "<span class='badge bg-warning'>EN COURS</span>" ?></td>
                <td><?= $folder["invoiced"] ? "<span class='badge bg-success'>OUI</span>" : "<span class='badge bg-danger'>NON</span>" ?></td>
                <td><?= $folder["id"]  ?> <?= $folder["type"] ?></td>
                <td><a target="_blank" href="<?= base_url("clients?r=" . $folder["invoice_to"]["id"]) ?>"><?= $folder["invoice_to"]["id"]  ?> <?= $folder["invoice_to"]["name"]  ?><i class="ti ti-link"></i></a></td>
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

<script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script>
  const table2 = new DataTable('.table', {
    order: [1, 'desc'],
    layout: {
      topStart: {
        buttons: [{
          extend: 'excelHtml5',
          text: '<i class="ti ti-download"></i>  Excel',
          className: "btn-success btn-sm d-flex align-items-center gap-1",
          filename: "Rapport des dossiers de transit du <?= date("d-m-Y", strtotime($_GET["from"] ?? null)) ?> au <?= date("d-m-Y", strtotime($_GET["to"] ?? null)) ?>"
        }]
      }
    }
  });
</script>
<?= $this->endSection(); ?>