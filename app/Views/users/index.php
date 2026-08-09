<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Utilisateurs
<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
Utilisateurs
<?= $this->endSection(); ?>
<?= $this->section('add'); ?>
<a href="<?= base_url("utilisateurs/ajouter") ?>" class="btn btn-success">
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
      <div class="card-title"><?= isset($_GET["r"]) ? "Résultat: " : "Liste des utilisateurs: " ?><?= count($users) ?> utilisateur(s)</div>
      <div class="table-responsive card-table">
        <table id="myTable" class="table table-vcenter">
          <thead>
            <tr>
              <th></th>
              <th>Nom</th>
              <th>Email</th>
              <th>Profil</th>
              <th>Date de création</th>
              <th>Dernière modification</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($users as $user) : ?>
              <tr>
                <td>
                  <div class="dropdown open">
                    <button class="btn dropdown-toggle" type="button" id="triggerId<?= $user["id"] ?>" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="ti ti-settings"></i>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="triggerId<?= $user["id"] ?>">
                      <a class="dropdown-item" href="<?= base_url("utilisateurs/modifier/" . $user["id"]) ?>">Modifier</a>
                      <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#resetModal" onclick="setResetId(<?= $user['id'] ?>)">Réinitialiser</button>
                      <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#deleteModal" onclick="setDeleteId(<?= $user['id'] ?>)">Supprimer</button>
                    </div>
                  </div>
                </td>
                <td><?= $user["name"] ?></td>
                <td><?= $user["email"] ?></td>
                <td><?= $user["profile"] ?></td>
                <td data-order="<?= strtotime($user["created_at"]) ?>"><?= $user["created_at"] ? date("d/m/Y H:i:s", strtotime($user["created_at"])) : "-" ?></td>
                <td data-order="<?= strtotime($user["updated_at"]) ?>"><?= $user["updated_at"] ? date("d/m/Y H:i:s", strtotime($user["updated_at"])) : "-" ?></td>
              </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>


<div class="modal fade modal-blur" id="resetModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleId" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          Réinitialiser le mot de passe
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?= form_open(
          "utilisateurs/modifier",
          [
            "id" => "resetForm"
          ]
        ) ?>
        <?= csrf_field() ?>
        <input type="text" name="id" id="resetId" hidden value="">
        <div>
          <label class="form-label">
            Mot de passe
          </label>
          <div class="input-group input-group-flat">
            <input type="password" name="password" required value="<?= set_value("password") ?>" id="password" class="form-control" placeholder="nouveau mot de passe">
            <span class="input-group-text">
              <a href="#" id="togglePassword" class="link-secondary" title="Afficher/Cacher" data-bs-toggle="tooltip">
                <svg xmlns="http://www.w3.org/2000/svg" class="icon" width="24" height="24" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
                  <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                  <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                  <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                </svg>
              </a>
            </span>
          </div>
        </div>

        <?= form_close() ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          Fermer
        </button>
        <button form="resetForm" type="submit" class="btn btn-primary">Enregistrer</button>
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
        base_url("utilisateurs/supprimer"),
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
  const resetModal = new bootstrap.Modal(
    document.getElementById("resetModal"),
    options,
  );
</script>
<script>
  document.getElementById("togglePassword").addEventListener("click", () => {
    const password = document.getElementById("password")
    const actualType = password.getAttribute("type")
    password.setAttribute("type", actualType == "password" ? "text" : "password");
  })
</script>
<script>
  const setResetId = (id) => {
    document.getElementById("resetId").value = id;
  }
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