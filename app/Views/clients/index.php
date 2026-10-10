<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Clients
<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
Clients
<?= $this->endSection(); ?>
<?= $this->section('add'); ?>
<a href="<?= base_url("clients/ajouter") ?>" class="btn btn-success">
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
      <div class="card-title"><?= isset($_GET["r"]) ? "Résultat: " : "Liste des clients: " ?><?= count($clients) ?> utilisateur(s)</div>
      <div class="table-responsive card-table">
        <table id="myTable" class="table table-vcenter">
          <thead>
            <tr>
              <th></th>
              <th>Nom</th>
              <th>Numéro de compte</th>
              <th>Email</th>
              <th>Téléphone</th>
              <th>NINEA</th>
              <th>PPM</th>
              <th>Date de création</th>
              <th>Dernière modification</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($clients as $client) : ?>
              <tr>
                <td>
                  <div class="dropdown open">
                    <button class="btn dropdown-toggle" type="button" id="triggerId<?= $client["id"] ?>" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="ti ti-settings"></i>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="triggerId<?= $client["id"] ?>">
                      <a class="dropdown-item" href="<?= base_url("clients/modifier/" . $client["id"]) ?>">Modifier</a>
                      <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#deleteModal" onclick="setDeleteId('<?= $client['id'] ?>')">Supprimer</button>
                    </div>
                  </div>
                </td>
                <td><?= $client["name"] ?></td>
                <td><?= $client["account_number"] ?></td>
                <td><?= $client["email"] ?></td>
                <td><?= $client["phone"] ?></td>
                <td><?= esc($client["ninea"] ?? "") ?: "-" ?></td>
                <td><?= esc($client["ppm"] ?? "") ?: "-" ?></td>
                <td data-order="<?= strtotime($client["created_at"]) ?>"><?= $client["created_at"] ? date("d/m/Y H:i:s", strtotime($client["created_at"])) : "-" ?></td>
                <td data-order="<?= strtotime($client["updated_at"]) ?>"><?= $client["updated_at"] ? date("d/m/Y H:i:s", strtotime($client["updated_at"])) : "-" ?></td>
              </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>



<div class="modal fade modal-blur" id="deleteModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          Suppression de compte utilisateur
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">Voulez-vous vraiment supprimer ce compte?</div>
      <?= form_open(
        base_url("clients/supprimer"),
        [
          "id" => "deleteForm"
        ]
      ) ?>
      <input type="text" name="id" hidden id="deleteId">
      <?= csrf_field() ?>
      <?= form_close() ?>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          Fermer
        </button>
        <button form="deleteForm" type="submit" class="btn btn-danger">Oui, supprimer!</button>
      </div>
    </div>
  </div>
</div>








<?= $this->endSection(); ?>
<?= $this->section('js'); ?>
<script>
  let table = new DataTable('#myTable');
</script>
<script>
  const setDeleteId = (id) => {
    document.getElementById("deleteId").value = id;
  }
</script>
<script>
  const myModal = new bootstrap.Modal(
    document.getElementById("deleteModal"),
    options,
  );
</script>

<?= $this->endSection(); ?>