<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Ajouter dossiers
<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
Dossiers
<?= $this->endSection(); ?>
<?= $this->section('cols'); ?>


<div class="col">
  <div class="card">
    <div class="card-body">
      <?= form_open("dossiers/ajouter") ?>
      <?= csrf_field() ?>
      <div class="card-title">Formulaire d'ouverture de dossiers</div>
      <div class="row">
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="id" class="form-label">Nº de dossier</label>
            <input required type="text" class="form-control" name="id" id="id" value="<?= set_value("id", "") ?>" placeholder="Automatique" readonly />
          </div>
          <div class="alert alert-primary" role="alert">
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" id="automation" checked />
              <label class="form-check-label" for="automation">Numérotation automatique</label>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="type" class="form-label">Type*</label>
            <select class="form-select" name="type" id="type">
              <option value="IMP" <?= set_select('type', "IMP", true) ?>>IMPORT</option>
              <option value="EXP" <?= set_select('type', "EXP") ?>>EXPORT</option>
            </select>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="open_date" class="form-label">Date d'ouverture*</label>
            <input required type="date" class="form-control" name="open_date" id="open_date" value="<?= set_value("open_date", "") ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="handling_agent" class="form-label">Agent en charge</label>
            <input type="text" class="form-control" name="handling_agent" id="handling_agent" value="<?= set_value("handling_agent", "") ?>" placeholder="John Ndiaye" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="repository" class="form-label">Répertoire</label>
            <input type="text" class="form-control" name="repository" id="repository" value="<?= set_value("repository", "") ?>" placeholder="XXXXXXX" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="orbus_number" class="form-label">Nº Orbus</label>
            <input type="text" class="form-control" name="orbus_number" id="orbus_number" value="<?= set_value("orbus_number", "") ?>" placeholder="XXXXXXXX" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="expeditor" class="form-label">Expéditeur</label>
            <input type="text" class="form-control" name="expeditor" id="expeditor" value="<?= set_value("expeditor", "") ?>" placeholder="Exp" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="bl" class="form-label">CNT/LTA*</label>
            <input required type="text" class="form-control" name="bl" id="bl" value="<?= set_value("bl", "") ?>" placeholder="XXXXXXXX" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="bl_of" class="form-label">Date CNT/LTA*</label>
            <input required type="date" class="form-control" name="bl_of" id="bl_of" value="<?= set_value("bl_of", "") ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="boat" class="form-label">Navire*</label>
            <input required type="text" class="form-control" name="boat" id="boat" value="<?= set_value("boat", "") ?>" placeholder="XXXXXXXX" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="boat_of" class="form-label">Date Navire*</label>
            <input required type="date" class="form-control" name="boat_of" id="boat_of" value="<?= set_value("boat_of", "") ?>" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="manifest" class="form-label">Manifeste</label>
            <input type="text" class="form-control" name="manifest" id="manifest" value="<?= set_value("manifest", "") ?>" placeholder="XXXXXXXX" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="article" class="form-label">Article</label>
            <input type="text" class="form-control" name="article" id="article" value="<?= set_value("article", "") ?>" placeholder="XXXXXXXX" />
          </div>
        </div>
        <div class="col-12">
          <div class="mb-3">
            <label for="declaration" class="form-label">Déclaration</label>
            <textarea class="form-control" name="declaration" id="declaration" rows="3"></textarea>
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="recipient" class="form-label">Destinataire</label>
            <input type="text" class="form-control" name="recipient" id="recipient" value="<?= set_value("recipient", "") ?>" placeholder="XXXXXXXX" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="recipient_address" class="form-label">Adresse du destinataire</label>
            <input type="text" class="form-control" name="recipient_address" id="recipient_address" value="<?= set_value("recipient_address", "") ?>" placeholder="XXXXXXXX" />
          </div>
        </div>
        <div class="col-md-6 col-lg-4">
          <div class="mb-3">
            <label for="" class="form-label">À facturer à</label>
            <select class="form-select" name="invoice_to" required id="invoice_to">
              <?php foreach ($clients as $client) : ?>
                <option value="<?= $client["id"] ?>" <?= set_select("invoice_to", $client["id"]) ?>>Nº<?= $client["id"] ?> <?= $client["name"] ?></option>
              <?php endforeach ?>
            </select>
          </div>

        </div>


        <div class="col-12 mx-auto text-center">
          <button type="submit" class="btn btn-primary">
            Créer le dossier
          </button>
        </div>
      </div>
      <?= form_close() ?>
    </div>
  </div>
</div>
<?= $this->endSection(); ?>
<?= $this->section('js'); ?>
<script>
  const id = document.getElementById("id")
  document.getElementById("automation").addEventListener("change", e => {
    if (e.target.checked) {
      id.setAttribute("readonly", true);
      id.setAttribute("placeholder", "Automatique");
      id.value = "";
    } else {
      id.removeAttribute("readonly");
      id.setAttribute("placeholder", "AAAAMMXXXXX");
      id.setAttribute("required", true);
    }
  })
</script>
<?= $this->endSection(); ?>