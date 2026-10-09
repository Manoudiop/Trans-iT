<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Corbeille
<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
Corbeille des dossiers
<?= $this->endSection(); ?>
<?= $this->section('add'); ?>
<a href="<?= base_url("dossiers") ?>" class="btn">Retour aux dossiers</a>
<?= $this->endSection(); ?>
<?= $this->section('cols'); ?>

<div class="col">
  <div class="card">
    <div class="card-body">
      <div class="card-title"><?= count($folders) ?> dossier(s) supprimé(s)</div>

      <p class="text-muted">
        Un dossier supprimé conserve ses colis, ses pièces jointes et son numéro de
        connaissement. Pour réutiliser ce connaissement, restaurez le dossier plutôt
        que d'en ressaisir un nouveau.
      </p>

      <?php if ($folders === []) : ?>
        <div class="alert alert-info mb-0" role="alert">La corbeille est vide.</div>
      <?php else : ?>
        <div class="table-responsive card-table">
          <table class="table table-vcenter">
            <thead>
              <tr>
                <th>Nº</th>
                <th>Connaissement</th>
                <th>Client</th>
                <th>Ouvert le</th>
                <th>Supprimé le</th>
                <th>Facturé?</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($folders as $folder) : ?>
                <tr>
                  <td><?= esc($folder["id"]) ?> <?= esc($folder["type"]) ?></td>
                  <td><?= esc($folder["bl"]) ?></td>
                  <td><?= esc($folder["invoice_to"]["name"]) ?></td>
                  <td><?= $folder["open_date"] ? date("d/m/Y", strtotime($folder["open_date"])) : "-" ?></td>
                  <td><?= $folder["deleted_at"] ? date("d/m/Y H:i", strtotime($folder["deleted_at"])) : "-" ?></td>
                  <td>
                    <?php if ($folder["invoiced"]) : ?>
                      <span class="badge bg-success">OUI</span>
                    <?php else : ?>
                      <span class="badge bg-danger">NON</span>
                    <?php endif ?>
                  </td>
                  <td class="text-end">
                    <?= form_open("dossiers/restaurer") ?>
                    <input type="hidden" name="id" value="<?= esc($folder["id"]) ?>">
                    <button type="submit" class="btn btn-sm btn-outline-success">Restaurer</button>
                    <?= form_close() ?>
                  </td>
                </tr>
              <?php endforeach ?>
            </tbody>
          </table>
        </div>
      <?php endif ?>
    </div>
  </div>
</div>

<?= $this->endSection(); ?>
