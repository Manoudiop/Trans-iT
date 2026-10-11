<?php
/**
 * Pied de liste paginée: la position dans l'ensemble, puis les pages.
 *
 * « 21 à 40 sur 303 » répond à une question que de simples numéros de page
 * laissent ouverte, surtout après une recherche.
 *
 * @var CodeIgniter\Pager\Pager $pager
 */
$details = $pager->getDetails();
$total = (int) $details["total"];
$parPage = (int) $details["perPage"];
$page = (int) $details["currentPage"];

$debut = $total === 0 ? 0 : ($page - 1) * $parPage + 1;
$fin = min($page * $parPage, $total);
?>

<div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mt-2">
  <p class="m-0 text-muted">
    <?php if ($total === 0) : ?>
      Aucun résultat
    <?php else : ?>
      <?= number_format($debut, 0, ",", " ") ?> à <?= number_format($fin, 0, ",", " ") ?>
      sur <?= number_format($total, 0, ",", " ") ?>
    <?php endif ?>
  </p>

  <?= $pager->links("default", "transit") ?>
</div>
