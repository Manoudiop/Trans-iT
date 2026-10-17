<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Dossiers
<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
Dossiers
<?= $this->endSection(); ?>
<?= $this->section('add'); ?>
<a href="<?= base_url("dossiers/ajouter") ?>" class="btn btn-success">
  <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
    <path d="M12 5l0 14" />
    <path d="M5 12l14 0" />
  </svg>
  Ajouter
</a>
<?= $this->endSection(); ?>
<?= $this->section('cols'); ?>
<div class="col">
  <div class="card">
    <div class="card-body">
      <?php $url = base_url("dossiers"); ?>
      <div class="card-title">
        <?= $options->search !== "" ? "Résultat de la recherche" : "Liste des dossiers" ?>
      </div>

      <?php // view() et non $this->include(): le second paramètre de include()
      // est une liste d'options de rendu, pas des données, et le partiel ne
      // recevrait rien. ?>
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
              <th><?= entete_triable("Facturé ?", "facture", $options, $url) ?></th>
              <th><?= entete_triable("Nº", "numero", $options, $url) ?></th>
              <th>Clients</th>
              <th>Fichiers joints</th>
            </tr>
          </thead>
          <tbody>
            <?php if ($folders === []) : ?>
              <tr>
                <td colspan="7" class="text-center text-muted py-4">
                  <?= $options->search !== ""
                      ? "Aucun dossier ne correspond à « " . esc($options->search) . " »."
                      : "Aucun dossier." ?>
                </td>
              </tr>
            <?php endif ?>
            <?php foreach ($folders as $folder) : ?>
              <tr>
                <td>
                  <div class="dropdown open">
                    <button class="btn dropdown-toggle" type="button" id="triggerId<?= $folder["id"] ?>" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="ti ti-settings"></i>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="triggerId<?= $folder["id"] ?>">
                      <?php if (!$folder["invoiced"] and peut_facturer()) : ?>
                        <a class="dropdown-item" href="<?= base_url("factures/facturer/" . $folder["id"]) ?>">Facturer</a>
                      <?php endif ?>
                      <a class="dropdown-item" href="<?= base_url("dossiers/information/" . $folder["id"]) ?>">Consulter</a>
                      <a class="dropdown-item" href="<?= base_url("dossiers/imprimer/" . $folder["id"]) ?>">Imprimer</a>
                      <a class="dropdown-item  <?= $folder["closed"] ? "disabled" : "" ?>" href="<?= base_url("dossiers/supprimer/" . $folder["id"]) ?>">Supprimer</a>
                    </div>
                  </div>
                </td>
                <td><?= $folder["open_date"] ? date("d/m/Y", strtotime($folder["open_date"])) : "-" ?></td>
                <td><?= $folder["closed"] ? "<span class='badge bg-success'>TERMINÉ</span>" : "<span class='badge bg-warning'>EN COURS</span>" ?></td>
                <td><?= $folder["invoiced"] ? "<span class='badge bg-success'>OUI</span>" : "<span class='badge bg-danger'>NON</span>" ?></td>
                <td><?= $folder["id"]  ?> <?= $folder["type"] ?></td>
                <td><a href="<?= base_url("clients?r=" . $folder["invoice_to"]["id"]) ?>"><?= $folder["invoice_to"]["id"]  ?> <?= $folder["invoice_to"]["name"]  ?><i class="ti ti-link"></i></a></td>
                <td><?= count($folder["files"]) ?></td>
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