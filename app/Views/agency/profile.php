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
        <div class="col-md-8">
          <div class="mb-3">
            <label for="address" class="form-label">Adresse</label>
            <input type="text" class="form-control" name="address" id="address"
              value="<?= set_value("address", $agence["address"] ?? "") ?>" placeholder="Km 4,5 Boulevard du Centenaire, Dakar" />
          </div>
        </div>
        <div class="col-md-4">
          <div class="mb-3">
            <label for="city" class="form-label">Ville</label>
            <input type="text" class="form-control" name="city" id="city"
              value="<?= set_value("city", $agence["city"] ?? "") ?>" placeholder="Dakar" />
            <small class="form-hint">
              Pour la mention « Fait à … » au bas de la facture. Saisie à part:
              elle ne se découpe pas de façon fiable depuis l'adresse.
            </small>
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
        <div class="col-12">
          <div class="mb-3">
            <label for="payment_terms" class="form-label">Conditions de règlement</label>
            <textarea class="form-control" name="payment_terms" id="payment_terms" rows="3"
              placeholder="Règlement à 30 jours date de facture. Virement: CBAO SN012 01001 000123456789 12. Tout retard entraîne des pénalités au taux légal."><?= set_value("payment_terms", $agence["payment_terms"] ?? "") ?></textarea>
            <small class="form-hint">
              Imprimées au bas de chaque facture, sous le total. Délai, mode de
              règlement, coordonnées bancaires, pénalités: ce que vous voulez y voir.
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
      <div class="card-title">Logo</div>
      <div class="row align-items-center">
        <div class="col-md-4 text-center mb-3 mb-md-0">
          <?php if (agency_logo_url()) : ?>
            <img src="<?= agency_logo_url() ?>" alt="Logo de l'agence" style="max-height: 120px; max-width: 100%;">
          <?php else : ?>
            <div class="text-muted border rounded p-4">Aucun logo</div>
          <?php endif ?>
        </div>
        <div class="col-md-8">
          <?= form_open_multipart("agence/logo") ?>
          <?= csrf_field() ?>
          <div class="mb-2">
            <label for="logo" class="form-label">Déposer une image</label>
            <input type="file" class="form-control" name="logo" id="logo"
              accept="image/png,image/jpeg,image/gif,image/webp" required>
            <small class="form-hint">
              PNG, JPEG, GIF ou WebP, 500 Ko au maximum. Le SVG n'est pas accepté:
              il peut contenir du code. Le logo s'affiche dans le bandeau de
              l'application et sur vos factures.
            </small>
          </div>
          <button type="submit" class="btn btn-primary">Enregistrer le logo</button>
          <?= form_close() ?>
          <?php if (!empty($agence["logo_path"])) : ?>
            <?= form_open("agence/logo/supprimer", ["class" => "d-inline"]) ?>
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-danger mt-2">Retirer le logo</button>
            <?= form_close() ?>
          <?php endif ?>
        </div>
      </div>
    </div>
  </div>

  <div class="card mt-3">
    <div class="card-body">
      <div class="card-title">Aperçu de l'en-tête de facture</div>
      <div class="border rounded p-3 d-flex justify-content-between align-items-center">
        <div>
          <?php if (agency_logo_url()) : ?>
            <img src="<?= agency_logo_url() ?>" alt="" style="max-height: 70px;">
          <?php endif ?>
        </div>
        <div class="text-end">
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
