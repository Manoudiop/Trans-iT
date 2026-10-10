<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Mon agence
<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
Paramètres de l'agence
<?= $this->endSection(); ?>
<?= $this->section('cols'); ?>

<div class="col-12 d-block">
  <div class="card">
    <div class="card-body">
      <?= form_open("agence") ?>
      <?= csrf_field() ?>
      <div class="card-title">Identité de l'agence</div>
      <p class="text-muted">
        Ces informations constituent l'en-tête des documents remis à vos clients,
        la facture en premier. Le sous-domaine
        (<code><?= esc($agence["slug"]) ?></code>) et l'abonnement ne se modifient
        pas ici.
      </p>
      <div class="row">
        <div class="col-md-6">
          <div class="mb-3">
            <label for="name" class="form-label">Raison sociale*</label>
            <input required type="text" class="form-control" name="name" id="name"
              value="<?= set_value("name", $agence["name"]) ?>" placeholder="Transit Sénégal SARL" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label for="phone" class="form-label">Téléphone</label>
            <input type="tel" class="form-control" name="phone" id="phone"
              value="<?= set_value("phone", $agence["phone"] ?? "") ?>" placeholder="+221 33 000 00 00" />
          </div>
        </div>
        <div class="col-md-12">
          <div class="mb-3">
            <label for="address" class="form-label">Adresse</label>
            <input type="text" class="form-control" name="address" id="address"
              value="<?= set_value("address", $agence["address"] ?? "") ?>" placeholder="Km 4,5 Boulevard du Centenaire, Dakar" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label for="ninea" class="form-label">NINEA</label>
            <input type="text" class="form-control" name="ninea" id="ninea"
              value="<?= set_value("ninea", $agence["ninea"] ?? "") ?>" placeholder="0001234567 2G3" />
          </div>
        </div>
        <div class="col-md-6">
          <div class="mb-3">
            <label for="agreement_number" class="form-label">Agrément en douane</label>
            <input type="text" class="form-control" name="agreement_number" id="agreement_number"
              value="<?= set_value("agreement_number", $agence["agreement_number"] ?? "") ?>" placeholder="Nº de commissionnaire agréé" />
            <small class="form-hint">
              Reporté automatiquement sur les notes de détail. Un dossier dédouané
              sous l'agrément d'un confrère garde le sien, saisi sur le dossier.
            </small>
          </div>
        </div>
        <div class="col-12 text-center">
          <button type="submit" class="btn btn-primary">Enregistrer</button>
        </div>
      </div>
      <?= form_close() ?>
    </div>
  </div>

  <div class="card mt-3">
    <div class="card-body">
      <div class="card-title">Aperçu de l'en-tête de facture</div>
      <div class="border rounded p-3 text-end">
        <div class="h2 mb-0"><?= esc($agence["name"]) ?></div>
        <?php if (!empty($agence["address"])) : ?>
          <div><?= esc($agence["address"]) ?></div>
        <?php endif ?>
        <?php if (!empty($agence["phone"])) : ?>
          <div>Tél. <?= esc($agence["phone"]) ?></div>
        <?php endif ?>
        <?php if (!empty($agence["ninea"])) : ?>
          <div>NINEA <?= esc($agence["ninea"]) ?></div>
        <?php endif ?>
        <?php if (!empty($agence["agreement_number"])) : ?>
          <div>Agrément <?= esc($agence["agreement_number"]) ?></div>
        <?php endif ?>
      </div>
      <?php if (empty($agence["address"]) || empty($agence["ninea"])) : ?>
        <div class="text-muted small mt-2">
          Les lignes non renseignées n'apparaissent pas sur la facture.
        </div>
      <?php endif ?>
    </div>
  </div>
</div>

<?= $this->endSection(); ?>
