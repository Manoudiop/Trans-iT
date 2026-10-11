<?php
/**
 * Recherche et taille de page d'une liste paginée.
 *
 * La recherche part au serveur: depuis la pagination côté serveur, filtrer
 * dans le navigateur ne verrait que les lignes de la page affichée et
 * laisserait croire qu'il n'y a rien à trouver ailleurs.
 *
 * @var App\Libraries\ListOptions $options
 * @var string                    $url     Adresse de la liste
 * @var string                    $placeholder
 */
?>

<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
  <form action="<?= esc($url, "attr") ?>" method="get" class="d-flex gap-1">
    <?php // Le tri et la taille de page survivent à la recherche. ?>
    <input type="hidden" name="tri" value="<?= esc($options->sort, "attr") ?>">
    <input type="hidden" name="sens" value="<?= esc($options->dir, "attr") ?>">
    <input type="hidden" name="taille" value="<?= esc((string) $options->perPage, "attr") ?>">
    <div class="input-icon">
      <span class="input-icon-addon"><i class="ti ti-search"></i></span>
      <input type="text" name="r" class="form-control" style="min-width: 220px;"
             value="<?= esc($options->search) ?>"
             placeholder="<?= esc($placeholder ?? "Rechercher...") ?>">
    </div>
    <button type="submit" class="btn">Chercher</button>
    <?php if ($options->search !== "") : ?>
      <a href="<?= esc($url . "?" . http_build_query($options->query(["r" => null, "page" => null])), "attr") ?>"
         class="btn btn-ghost-secondary">Effacer</a>
    <?php endif ?>
  </form>

  <div class="d-flex align-items-center gap-1 text-muted">
    <span class="small">Lignes par page</span>
    <?php foreach (App\Libraries\ListOptions::TAILLES as $taille) : ?>
      <a href="<?= esc(lien_taille_page($taille, $options, $url), "attr") ?>"
         class="btn btn-sm <?= $options->perPage === $taille ? "btn-primary" : "btn-ghost-secondary" ?>">
        <?= $taille ?>
      </a>
    <?php endforeach ?>
  </div>
</div>
