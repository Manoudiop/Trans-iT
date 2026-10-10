<?= $this->extend('layouts/app'); ?>
<?= $this->section('title'); ?>
Console
<?= $this->endSection(); ?>
<?= $this->section('h1'); ?>
Console d'exploitation
<?= $this->endSection(); ?>
<?= $this->section('cols'); ?>

<div class="col-12 d-block">
  <div class="card">
    <div class="card-body">
      <div class="card-title">
        Agences <span class="badge bg-secondary"><?= count($tenants) ?></span>
      </div>

      <div class="table-responsive">
        <table class="table table-vcenter">
          <thead>
            <tr>
              <th>Agence</th>
              <th>Adresse</th>
              <th>Offre</th>
              <th class="text-end">Utilisateurs</th>
              <th class="text-end">Dossiers</th>
              <th class="text-end">Stockage</th>
              <th>État</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($tenants as $tenant) : ?>
              <tr>
                <td><?= esc($tenant["name"]) ?></td>
                <td><code><?= esc($tenant["slug"]) ?></code></td>
                <td>
                  <?= form_open("console/offre", ["class" => "d-flex gap-1"]) ?>
                  <input type="hidden" name="id" value="<?= esc($tenant["id"]) ?>">
                  <select name="plan_id" class="form-select form-select-sm">
                    <?php foreach ($plans as $plan) : ?>
                      <option value="<?= esc($plan["id"]) ?>"
                        <?= (int) $tenant["plan_id"] === (int) $plan["id"] ? "selected" : "" ?>>
                        <?= esc($plan["name"]) ?>
                      </option>
                    <?php endforeach ?>
                  </select>
                  <button type="submit" class="btn btn-sm">OK</button>
                  <?= form_close() ?>
                </td>
                <td class="text-end"><?= esc($tenant["users_count"]) ?></td>
                <td class="text-end"><?= esc($tenant["folders_count"]) ?></td>
                <td class="text-end"><?= number_format($tenant["storage_bytes"] / 1048576, 1, ",", " ") ?> Mo</td>
                <td>
                  <?php if ($tenant["active"]) : ?>
                    <span class="badge bg-success">ACTIVE</span>
                  <?php else : ?>
                    <span class="badge bg-danger">SUSPENDUE</span>
                  <?php endif ?>
                </td>
                <td class="text-end">
                  <?= form_open("console/suspendre") ?>
                  <input type="hidden" name="id" value="<?= esc($tenant["id"]) ?>">
                  <button type="submit" class="btn btn-sm <?= $tenant["active"] ? "btn-outline-danger" : "btn-outline-success" ?>">
                    <?= $tenant["active"] ? "Suspendre" : "Réactiver" ?>
                  </button>
                  <?= form_close() ?>
                </td>
              </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="card mt-3">
    <div class="card-body">
      <div class="card-title">Offres</div>
      <div class="table-responsive">
        <table class="table table-vcenter">
          <thead>
            <tr>
              <th>Offre</th>
              <th class="text-end">Utilisateurs</th>
              <th class="text-end">Dossiers / mois</th>
              <th class="text-end">Stockage</th>
              <th class="text-end">Prix mensuel</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($plans as $plan) : ?>
              <tr>
                <td><?= esc($plan["name"]) ?></td>
                <td class="text-end"><?= $plan["max_users"] === null ? "illimité" : esc($plan["max_users"]) ?></td>
                <td class="text-end"><?= $plan["max_folders_per_month"] === null ? "illimité" : esc($plan["max_folders_per_month"]) ?></td>
                <td class="text-end"><?= $plan["max_storage_mb"] === null ? "illimité" : esc($plan["max_storage_mb"]) . " Mo" ?></td>
                <td class="text-end"><?= $plan["price_cfa"] ? number_format((float) $plan["price_cfa"], 0, ",", " ") . " FCFA" : "gratuit" ?></td>
              </tr>
            <?php endforeach ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection(); ?>
