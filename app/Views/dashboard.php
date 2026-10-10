<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Tableau de bord
<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
Tableau de bord
<?= $this->endSection(); ?>
<?= $this->section('cols'); ?>
<div class="col-12">
  <div class="row row-cards">
    <div class="col-sm-6 col-lg-3">
      <div class="card card-sm">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-auto">
              <span class="bg-primary text-white avatar"><!-- Download SVG icon from http://tabler-icons.io/i/currency-dollar -->
                <i class="ti ti-report-money fs-1"></i>
              </span>
            </div>
            <div class="col">
              <div class="font-weight-medium">
                Facturé du mois
              </div>
              <div class="fs-2">
                <?= number_format($sales_figures, 0, ",", " ") ?> FCFA
              </div>
              <div class="text-muted small">
                dont produit <strong><?= number_format($revenue_figures, 0, ",", " ") ?> FCFA</strong>
                <?php if ($sales_figures > 0) : ?>
                  (<?= round($revenue_figures / $sales_figures * 100) ?> %)
                <?php endif ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card card-sm">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-auto">
              <span class="bg-green text-white avatar"><!-- Download SVG icon from http://tabler-icons.io/i/shopping-cart -->
                <i class="ti ti-users fs-1"></i>
              </span>
            </div>
            <div class="col">
              <div class="font-weight-medium">
                Nombre de clients
              </div>
              <div class="fs-2">
                <?= $clients_count ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card card-sm">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-auto">
              <span class="bg-twitter text-white avatar"><!-- Download SVG icon from http://tabler-icons.io/i/brand-twitter -->
                <i class="ti ti-folder-cog fs-1"></i>
              </span>
            </div>
            <div class="col">
              <div class="font-weight-medium">
                Nombre de dossiers en cours
              </div>
              <div class="fs-2">
                <?= $in_progress_folders_count ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="col-sm-6 col-lg-3">
      <div class="card card-sm">
        <div class="card-body">
          <div class="row align-items-center">
            <div class="col-auto">
              <span class="bg-facebook text-white avatar"><!-- Download SVG icon from http://tabler-icons.io/i/brand-facebook -->
                <i class="ti ti-folders fs-1"></i>
              </span>
            </div>
            <div class="col">
              <div class="font-weight-medium">
                Nombre de dossiers du mois
              </div>
              <div class="fs-2">
                <?= $month_folder ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<div class="col-sm-6 col-lg-4 d-flex">
  <div class="card card-sm flex-fill">
    <div class="card-body">
      <div class="card-title">Répartition des types de dossiers traités</div>
      <div id="imp-exp-pie" class="d-flex align-items-center justify-content-center"></div>
    </div>
  </div>
</div>
<div class="col-sm-6 col-lg-4 d-flex">
  <div class="card card-sm flex-fill">
    <div class="card-body">
      <div class="card-title">Évolution du chiffre d'affaires</div>
      <div id="chart-sales"></div>
    </div>
  </div>
</div>
<div class="col-sm-6 col-lg-4 d-flex">
  <div class="card card-sm flex-fill">
    <div class="card-body">
      <div class="card-title">Évolution de la clientèle</div>
      <div id="chart-clients"></div>
    </div>
  </div>
</div>
<div class="col-md-6">
  <div class="card card-sm">
    <div class="card-body">
      <div class="card-title text-danger">Dossiers critiques (+72 Heures)</div>
      <div class="table-responsive card-table text-nowrap">
        <table id="criticals" class="table table-vcenter table-sm">
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
            <?php foreach ($criticals as $folder) : ?>
              <tr>
                <td>
                  <div class="dropdown open">
                    <button class="btn dropdown-toggle" type="button" id="triggerId<?= $folder["id"] ?>" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="ti ti-settings"></i>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="triggerId<?= $folder["id"] ?>">
                      <?php if (!$folder["invoiced"]) : ?>
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
<div class="col-md-6">
  <div class="card card-sm">
    <div class="card-body">
      <div class="card-title">En attente de facturation</div>
      <div class="table-responsive card-table text-nowrap">
        <table id="notInvoiced" class="table table-vcenter table-sm">
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
            <?php foreach ($notInvoiced as $invoice) : ?>
              <tr>
                <td>
                  <div class="dropdown open">
                    <button class="btn dropdown-toggle" type="button" id="triggerId<?= $invoice["id"] ?>" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="ti ti-settings"></i>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="triggerId<?= $invoice["id"] ?>">
                      <a class="dropdown-item" href="<?= base_url("factures/facturer/" . $invoice["id"]) ?>">Facturer</a>
                      <a class="dropdown-item" target="_blank" href="<?= base_url("dossiers/information/" . $invoice["id"]) ?>">Consulter le dossier</a>
                    </div>
                  </div>
                </td>
                <td><?= date("d/m/Y", strtotime($invoice["open_date"])) ?></td>
                <td><?= $invoice["closed"] ? "<span class='badge bg-success'>TERMINÉ</span>" : "<span class='badge bg-warning'>EN COURS</span>" ?></td>
                <td><?= $invoice["invoiced"] ? "<span class='badge bg-success'>OUI</span>" : "<span class='badge bg-danger'>NON</span>" ?></td>
                <td><?= $invoice["id"]  ?> <?= $invoice["type"] ?></td>
                <td><a target="_blank" href="<?= base_url("clients?r=" . $invoice["invoice_to"]["id"]) ?>"><?= $invoice["invoice_to"]["id"]  ?> <?= $invoice["invoice_to"]["name"]  ?><i class="ti ti-link"></i></a></td>
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
<!-- Libs JS -->
<script src="<?= base_url("pack/libs/apexcharts/dist/apexcharts.min.js?1684106062") ?>" defer></script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    window.ApexCharts &&
      new ApexCharts(document.getElementById("imp-exp-pie"), {
        chart: {
          type: "donut",
          fontFamily: "inherit",
          height: "300px",
          sparkline: {
            enabled: true,
          },
          animations: {
            enabled: true,
          },
        },
        toolbar: {
          show: true,
        },
        fill: {
          opacity: 1,
        },
        series: [<?= $pie_type_imp ?>, <?= $pie_type_exp ?>, ],
        labels: ["Import", "Export"],
        tooltip: {
          theme: "light",
        },
        grid: {
          strokeDashArray: 4,
        },
        colors: [
          tabler.getColor("danger"),
          tabler.getColor("success"),
        ],
        legend: {
          show: true,
          position: "bottom",
          offsetY: 12,
          markers: {
            width: 10,
            height: 10,
            radius: 100,
          },
          itemMargin: {
            horizontal: 8,
            vertical: 8,
          },
        },
        tooltip: {
          fillSeriesColor: true,
        },
      }).render();
  });
</script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    window.ApexCharts &&
      new ApexCharts(document.getElementById("chart-sales"), {
        chart: {
          type: "bar",
          fontFamily: "inherit",
          height: "300px",
          parentHeightOffset: 0,
          toolbar: {
            show: true,
          },
          animations: {
            enabled: true,
          },
        },
        plotOptions: {
          bar: {
            columnWidth: "50%",
          },
        },
        dataLabels: {
          enabled: false,
        },
        fill: {
          opacity: 1,
        },
        series: [{
          name: "Facturé (débours compris)",
          data: <?= json_encode($sales_chart) ?>,
        }, {
          name: "Produit de la maison",
          data: <?= json_encode($revenue_chart) ?>,
        }, ],
        tooltip: {
          theme: "light",
        },
        grid: {
          padding: {
            top: -20,
            right: 0,
            left: -4,
            bottom: -4,
          },
          strokeDashArray: 4,
        },
        xaxis: {
          labels: {
            padding: 0,
          },
          tooltip: {
            enabled: true,
          },
          axisBorder: {
            show: true,
          },
          categories: ["J", "F", "M", "A", "M", "J", "J", "A", "S", "O", "N", "D", ],
        },
        yaxis: {
          labels: {
            padding: 4,
          },
        },
        colors: [tabler.getColor("primary")],
        legend: {
          show: true,
        },
      }).render();
  });
</script>
<script>
  document.addEventListener("DOMContentLoaded", function() {
    window.ApexCharts &&
      new ApexCharts(document.getElementById("chart-clients"), {
        chart: {
          type: "line",
          fontFamily: "inherit",
          height: 300,
          parentHeightOffset: 0,
          toolbar: {
            show: true,
          },
          animations: {
            enabled: true,
          },
        },
        fill: {
          opacity: 1,
        },
        stroke: {
          width: 2,
          lineCap: "round",
          curve: "smooth",
        },
        series: [{
          name: "Nouveaux clients",
          data: <?= json_encode($clients_chart) ?>,
        }, ],
        tooltip: {
          theme: "light",
        },
        grid: {
          padding: {
            top: -20,
            right: 0,
            left: -4,
            bottom: -4,
          },
          strokeDashArray: 4,
        },
        xaxis: {
          labels: {
            padding: 0,
          },
          tooltip: {
            enabled: true,
          },
          type: "month",
        },
        yaxis: {
          labels: {
            padding: 4,
          },
        },
        labels: ["J", "F", "M", "A", "M", "J", "J", "A", "S", "O", "N", "D"],
        colors: [tabler.getColor("success")],
        legend: {
          show: true,
        },
      }).render();
  });
</script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/dataTables.buttons.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.bootstrap5.js"></script>
<script src="https://cdn.datatables.net/buttons/3.0.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script>
  const table = new DataTable('#criticals', {
    order: [1, 'desc'],
    layout: {
      topStart: {
        buttons: [{
          extend: 'excelHtml5',
          text: '<i class="ti ti-download"></i>  Excel',
          className: "btn-success btn-sm d-flex align-items-center gap-1",
          filename: "Liste des dossiers critiques - Généré le <?= date("d_m_Y") ?>"
        }]
      }
    }
  });
</script>
<script>
  const table2 = new DataTable('#notInvoiced', {
    order: [1, 'desc'],
    title: "juice",
    layout: {
      topStart: {
        buttons: [{
          extend: 'excelHtml5',
          text: '<i class="ti ti-download"></i>  Excel',
          className: "btn-success btn-sm d-flex align-items-center gap-1",
          filename: "Liste des dossiers non facturés - Généré le <?= date("d_m_Y") ?>"
        }]
      }
    }
  });
</script>
<?= $this->endSection(); ?>