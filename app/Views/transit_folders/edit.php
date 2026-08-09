<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Modification dossier Nº<?= $id ?>
<?= $this->endSection(); ?>
<?= $this->section('breadcrumb'); ?>
<nav class="breadcrumb">
  <a class="breadcrumb-item" href="<?= base_url('dossiers') ?>">Dossiers</a>
  <a class="breadcrumb-item" href="<?= base_url("dossiers/information/" . $id) ?>"><?= $id ?> <?= $type ?></a>
  <span class="breadcrumb-item active" aria-current="page">Modification</span>
</nav>

<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
<span>
  Dossier <span class="text-primary">Nº <?= $id ?> <?= $type ?></span>
</span>
<?= $this->endSection(); ?>
<?= $this->section('cols'); ?>

<div class="col-12">
  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="card-title mb-0">Colis (Total: <?= count($items) ?>)</div>
        <div class="card-title mb-0">
          <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMerch">
            Ajouter
          </button>
        </div>
      </div>
      <div class="table-responsive">
        <table id="merchTable" class="table table-vcenter">
          <thead>
            <tr>
              <th></th>
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
                <td>
                  <div class="dropdown">
                    <button class="btn dropdown-toggle" type="button" id="triggerId<?= $item["id"] ?>" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="ti ti-settings"></i>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="triggerId<?= $item["id"] ?>">
                      <a class="dropdown-item" onclick='setEditItem(<?= json_encode($item) ?>)' data-bs-toggle="modal" data-bs-target="#editMerch" href="#">Modifier</a>
                      <a class="dropdown-item text-danger" onclick="setDeleteItem(<?= $item['id'] ?>)" data-bs-toggle="modal" data-bs-target="#deleteItem" href="#">Supprimer</a>
                    </div>
                  </div>
                </td>
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

<div class="col-12">
  <div class="card">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="card-title mb-0">Fichiers joints (Total: <?= count($files) ?>)</div>
        <div class="card-title mb-0">
          <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#filesModal">
            Ajouter
          </button>
        </div>
      </div>
      <div class="table-responsive">
        <table id="merchTable" class="table table-vcenter">
          <thead>
            <tr>
              <th></th>
              <th>Nom du fichier</th>
              <th>Date d'ajout</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($files as $file) : ?>
              <tr>
                <td>
                  <div class="dropdown">
                    <button class="btn dropdown-toggle" type="button" id="triggerId<?= $file["id"] ?>" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <i class="ti ti-settings"></i>
                    </button>
                    <div class="dropdown-menu" aria-labelledby="triggerId<?= $file["id"] ?>">
                      <a class="dropdown-item" href="<?= $file["url"] ?>">Afficher</a>
                      <a class="dropdown-item" download href="<?= $file["url"] ?>">Télécharger</a>
                      <a class="dropdown-item text-danger" onclick="setDeleteFile(<?= $file['id'] ?>)" href="#" data-bs-toggle="modal" data-bs-target="#deleteFile">Supprimer</a>
                    </div>
                  </div>
                </td>
                <td><?= $file["name"] ?></td>
                <td><?= date("d/m/Y H:i:s", strtotime($file["created_at"])) ?></td>
              </tr>
            <?php endforeach ?>

          </tbody>
        </table>
      </div>


    </div>
  </div>
</div>

<div class="col-12">
  <div class="card">
    <div class="card-body">
      <?= form_open("dossiers/modifier") ?>
      <?= csrf_field() ?>
      <input type="text" name="id" value="<?= $id ?>" hidden>
      <div class="card-title">Informations d'ouverture</div>
      <div class="row">

        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="type" class="form-label">Type*</label>
            <select class="form-select" name="type" id="type">
              <option value="IMP" <?= set_select('type', "IMP", $type == "IMP") ?>>IMPORT</option>
              <option value="EXP" <?= set_select('type', "EXP", $type == "EXP") ?>>EXPORT</option>
            </select>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="open_date" class="form-label">Date d'ouverture*</label>
            <input required type="date" class="form-control" name="open_date" id="open_date" value="<?= set_value("open_date", $open_date) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="handling_agent" class="form-label">Agent traitant</label>
            <input type="text" class="form-control" name="handling_agent" id="handling_agent" value="<?= set_value("handling_agent", $handling_agent) ?>" placeholder="John Ndiaye" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="repository" class="form-label">Répertoire</label>
            <input type="text" class="form-control" name="repository" id="repository" value="<?= set_value("repository", $repository) ?>" placeholder="XXXXXXX" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="orbus_number" class="form-label">Nº Orbus</label>
            <input type="text" class="form-control" name="orbus_number" id="orbus_number" value="<?= set_value("orbus_number", $orbus_number) ?>" placeholder="XXXXXXXX" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="expeditor" class="form-label">Expéditeur</label>
            <input type="text" class="form-control" name="expeditor" id="expeditor" value="<?= set_value("expeditor", $expeditor) ?>" placeholder="Exp" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="bl" class="form-label">CNT/LTA*</label>
            <input required type="text" class="form-control" name="bl" id="bl" value="<?= set_value("bl", $bl) ?>" placeholder="XXXXXXXX" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="bl_of" class="form-label">Date CNT/LTA*</label>
            <input required type="date" class="form-control" name="bl_of" id="bl_of" value="<?= set_value("bl_of", $bl_of) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="boat" class="form-label">Navire*</label>
            <input required type="text" class="form-control" name="boat" id="boat" value="<?= set_value("boat", $boat) ?>" placeholder="XXXXXXXX" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="boat_of" class="form-label">Date Navire*</label>
            <input required type="date" class="form-control" name="boat_of" id="boat_of" value="<?= set_value("boat_of", $boat_of) ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="manifest" class="form-label">Manifeste</label>
            <input type="text" class="form-control" name="manifest" id="manifest" value="<?= set_value("manifest", $manifest) ?>" placeholder="XXXXXXXX" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="article" class="form-label">Article</label>
            <input type="text" class="form-control" name="article" id="article" value="<?= set_value("article", $article) ?>" placeholder="XXXXXXXX" />
          </div>
        </div>
        <div class="col-12">
          <div class="mb-3">
            <label for="declaration" class="form-label">Déclaration</label>
            <textarea class="form-control" name="declaration" id="declaration" rows="3"><?= $declaration ?></textarea>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="recipient" class="form-label">Destinataire</label>
            <input type="text" class="form-control" name="recipient" id="recipient" value="<?= set_value("recipient", $recipient) ?>" placeholder="XXXXXXXX" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="recipient_address" class="form-label">Adresse du destinataire</label>
            <input type="text" class="form-control" name="recipient_address" id="recipient_address" value="<?= set_value("recipient_address", $recipient_address) ?>" placeholder="XXXXXXXX" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="" class="form-label">À facturer à</label>
            <select class="form-select" name="invoice_to" id="invoice_to">
              <?php foreach ($clients as $client) : ?>
                <option value="<?= $client["id"] ?>" <?= set_select("invoice_to", $client["id"], $invoice_to["id"] == $client["id"]) ?>>Nº<?= $client["id"] ?> <?= $client["name"] ?></option>
              <?php endforeach ?>
            </select>
          </div>

        </div>


        <div class="col-12 mx-auto text-center">
          <button type="submit" class="btn btn-primary">
            Modifier
          </button>
        </div>
      </div>
      <?= form_close() ?>
    </div>
  </div>
</div>

<div class="col-12">
  <div class="card">
    <div class="card-body">
      <div class="card-title">Traitement en douane</div>
      <?= form_open("dossiers/modifier") ?>
      <?= csrf_field() ?>
      <input type="text" name="id" value="<?= $id ?>" hidden>
      <div class="row">
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="customs_admission_date" class="form-label">Admis douane</label>
            <input type="date" class="form-control" name="customs_admission_date" id="customs_admission_date" value="<?= $customs_admission_date ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="customs_inspector" class="form-label">Inspecteur traitant</label>
            <input type="text" class="form-control" name="customs_inspector" id="customs_inspector" placeholder="XXXXXXX" value="<?= $customs_inspector ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="bae_date" class="form-label">Date BAE</label>
            <input type="date" class="form-control" name="bae_date" id="bae_date" value="<?= $bae_date ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="delivery_date" class="form-label">Date de livriaison</label>
            <input type="date" class="form-control" name="delivery_date" id="delivery_date" value="<?= $delivery_date ?>" />
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label for="reserve" class="form-label">Réserve</label>
            <textarea class="form-control" name="reserve" id="reserve" rows="3"><?= $reserve ?></textarea>
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label for="missing" class="form-label">Manquant</label>
            <textarea class="form-control" name="missing" id="missing" rows="3"><?= $missing ?></textarea>
          </div>
        </div>
        <div class="col-12 mx-auto text-center">
          <button type="submit" class="btn btn-primary">
            Modifier
          </button>
        </div>
      </div>
      <?= form_close() ?>
    </div>
  </div>
</div>

<div class="col-12">
  <div class="card">
    <div class="card-body">
      <div class="card-title">Informations de fermeture</div>
      <?= form_open("dossiers/modifier") ?>
      <?= csrf_field() ?>
      <input type="text" name="id" value="<?= $id ?>" hidden>
      <div class="row">
        <?php if (session()->userData["profile"] == "ADMIN") : ?>
          <div class="col-md-6 col-lg-4">
            <div>Dossier fermé?</div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="closed" <?= $closed ? "checked" : "" ?> id="yes" value="1" />
              <label class="form-check-label" for="yes">Oui</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="closed" <?= !$closed ? "checked" : "" ?> id="non" value="0" />
              <label class="form-check-label" for="non">Non</label>
            </div>
          </div>
        <?php endif ?>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="transit_order" class="form-label">Ordre de transit</label>
            <input type="text" class="form-control" name="transit_order" id="transit_order" value="<?= $transit_order ?>" placeholder="XXXXXXX" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="transit_order_date" class="form-label">Date d'ordre de transit</label>
            <input type="date" class="form-control" name="transit_order_date" id="transit_order_date" value="<?= $transit_order_date ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="invoice" class="form-label">Nº de facture</label>
            <input type="text" class="form-control" name="invoice" id="invoice" placeholder="XXXXXXX" value="<?= $invoice ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="invoice_date" class="form-label">Date de facturation</label>
            <input type="date" class="form-control" name="invoice_date" id="invoice_date" value="<?= $invoice_date ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="receipt" class="form-label">Reçu</label>
            <input type="text" class="form-control" name="receipt" id="receipt" placeholder="XXXXXXX" value="<?= $receipt ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="receipt_date" class="form-label">Date du reçu</label>
            <input type="date" class="form-control" name="receipt_date" id="receipt_date" value="<?= $receipt_date ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="check" class="form-label">Chèque</label>
            <input type="text" class="form-control" name="check" id="check" placeholder="XXXXXXX" value="<?= $check ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="check_date" class="form-label">Date du chèque</label>
            <input type="date" class="form-control" name="check_date" id="check_date" value="<?= $check_date ?>" />
          </div>
        </div>
        <div class="col-12 mx-auto text-center">
          <button type="submit" class="btn btn-primary">
            Modifier
          </button>
        </div>
      </div>
      <?= form_close() ?>
    </div>
  </div>
</div>

<div class="modal fade modal-blur" id="addMerch" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="modalTitleIdMersh" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitleIdMersh">
          Ajouter un colis
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form action="<?= base_url("dossiers/ajouter-colis") ?>" id="addItems" method="post">
          <?= csrf_field() ?>
          <input type="text" name="folder_id" value="<?= $id ?>" hidden>
          <div class="mb-3">
            <label class="form-label">Marque</label>
            <input type="text" class="form-control" name="brand" />
          </div>
          <div class="mb-3">
            <label class="form-label">Quantité</label>
            <input type="number" min="1" class="form-control" name="quantity" />
          </div>
          <div class="mb-3">
            <label class="form-label">Nature</label>
            <input type="text" class="form-control" name="nature" />
          </div>
          <div class="mb-3">
            <label class="form-label">Poids en Kilogramme</label>
            <input type="number" min="0" class="form-control" name="weight" />
          </div>
          <div>
            <label class="form-label">Volume</label>
            <input type="text" class="form-control" name="volume" />
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          Fermer
        </button>
        <button type="submit" form="addItems" class="btn btn-primary">Ajouter</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteItem" tabindex="-1" role="dialog" aria-labelledby="modalTitleIdDeleteItem" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitleIdDeleteItem">
          Suppression de colis
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="container-fluid">
          <?= form_open(base_url("dossiers/supprimer-colis"), [
            'id' => 'supprimer-colis'
          ]) ?>
          <?= csrf_field() ?>
          <input type="text" id="toDeleteItem" name="id" hidden>
          Confirmez-vous la suppression du colis?
          <?= form_close() ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          Fermer
        </button>
        <button type="submit" form="supprimer-colis" class="btn btn-danger">Oui, supprimer!</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade modal-blur" id="editMerch" tabindex="-1" role="dialog" aria-labelledby="modalTitleIdEditMerch" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitleIdEditMerch">
          Modification de colis
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="container-fluid">
          <form action="<?= base_url("dossiers/ajouter-colis") ?>" id="editItems" method="post">
            <?= csrf_field() ?>
            <input type="text" name="id" id="id_mod" hidden>
            <div class="mb-3">
              <label class="form-label">Marque</label>
              <input type="text" class="form-control" name="brand" id="brand_mod" />
            </div>
            <div class="mb-3">
              <label class="form-label">Quantité</label>
              <input type="number" min="1" class="form-control" name="quantity" id="quantity_mod" />
            </div>
            <div class="mb-3">
              <label class="form-label">Nature</label>
              <input type="text" class="form-control" name="nature" id="nature_mod" />
            </div>
            <div class="mb-3">
              <label class="form-label">Poids en Kilogramme</label>
              <input type="number" min="0" class="form-control" name="weight" id="weight_mod" />
            </div>
            <div>
              <label class="form-label">Volume</label>
              <input type="text" class="form-control" name="volume" id="volume_mod" />
            </div>
          </form>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          Fermer
        </button>
        <button type="submit" form="editItems" class="btn btn-primary">Enregistrer</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="filesModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="addFileModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-md" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addFileModalTitle">
          Ajouter un fichier
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?= form_open_multipart("dossiers/ajouter-fichier", ["id" => "addFile"]) ?>
        <?= csrf_field() ?>
        <input type="text" name="folder_id" value="<?= $id ?>" hidden>
        <div class="mb-3">
          <label for="name" class="form-label">Nom du fichier</label>
          <input type="text" class="form-control" name="name" id="name" placeholder="Ex: BL" required />
        </div>
        <div class="mb-3">
          <label for="file" class="form-label">Fichier</label>
          <input type="file" class="form-control" name="file" id="file" required />
        </div>
        <?= form_close() ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          Fermer
        </button>
        <button type="submit" form="addFile" class="btn btn-primary">Ajouter</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteFile" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="deleteFile" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteFile">
          Suppression de fichier
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?= form_open("dossiers/supprimer-fichier", ["id" => "deleteFileForm"]) ?>
        <?= csrf_field() ?>
        <input type="text" name="id" id="toDeleteFile" hidden>
        <?= form_close() ?>
        <p>Supprimer ce fichier?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          Fermer
        </button>
        <button type="submit" form="deleteFileForm" class="btn btn-dangers">Suppirmer</button>
      </div>
    </div>
  </div>
</div>






<?= $this->endSection(); ?>
<?= $this->section('js'); ?>
<script>
  let table = new DataTable('#merchTable');
</script>

<script>
  const AddFileModal = new bootstrap.Modal(
    document.getElementById("filesModal"),
    options,
  );
</script>

<script>
  const deleteFileModal = new bootstrap.Modal(
    document.getElementById("deleteFile"),
    options,
  );
</script>

<script>
  const addMerch = new bootstrap.Modal(
    document.getElementById("addMerch"),
    options,
  );
</script>

<script>
  let merchTable = new DataTable('.table');
</script>

<script>
  var deleteItem = document.getElementById('deleteItem');
  deleteItem.addEventListener('show.bs.modal', function(event) {
    let button = event.relatedTarget;
    let recipient = button.getAttribute('data-bs-whatever');
  });
</script>

<script>
  const setDeleteItem = (id) => {
    console.log(id);
    document.getElementById("toDeleteItem").value = id;
  }
</script>


<script>
  const setDeleteFile = (id) => {
    console.log(id);
    document.getElementById("toDeleteFile").value = id;
  }
</script>

<script>
  var editMerch = document.getElementById('editMerch');

  editMerch.addEventListener('show.bs.modal', function(event) {
    // Button that triggered the modal
    let button = event.relatedTarget;
    // Extract info from data-bs-* attributes
    let recipient = button.getAttribute('data-bs-whatever');

    // Use above variables to manipulate the DOM
  });
</script>

<script>
  const setEditItem = (data) => {
    console.log("uankee", data.id);
    document.getElementById("id_mod").value = data.id
    document.getElementById("brand_mod").value = data.brand
    document.getElementById("quantity_mod").value = data.quantity
    document.getElementById("nature_mod").value = data.nature
    document.getElementById("weight_mod").value = data.weight
    document.getElementById("volume_mod").value = data.volume
  }
</script>


<?= $this->endSection(); ?>