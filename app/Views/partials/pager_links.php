<?php
/**
 * Numéros de page, au style de Tabler.
 *
 * Le gabarit fourni par CodeIgniter n'ajoute ni page-item ni page-link:
 * sa pagination sort du kit sans aucun style. Celui-ci les pose.
 *
 * @var CodeIgniter\Pager\PagerRenderer $pager
 */
$pager->setSurroundCount(2);
?>

<ul class="pagination m-0">
  <li class="page-item <?= $pager->hasPrevious() ? "" : "disabled" ?>">
    <a class="page-link" href="<?= $pager->hasPrevious() ? $pager->getPrevious() : "#" ?>" aria-label="Page précédente">
      <i class="ti ti-chevron-left"></i>
    </a>
  </li>

  <?php // Première et dernière pages toujours atteignables: sur seize pages,
  // atteindre la fin ne doit pas demander quinze clics.
  //
  // getFirstPageNumber() et getLastPageNumber() bornent la fenêtre
  // affichée, pas l'ensemble: le nombre total de pages se lit sur
  // getPageCount(). ?>
  <?php if ($pager->getFirstPageNumber() > 1) : ?>
    <li class="page-item"><a class="page-link" href="<?= $pager->getFirst() ?>">1</a></li>
    <li class="page-item disabled"><span class="page-link">…</span></li>
  <?php endif ?>

  <?php foreach ($pager->links() as $link) : ?>
    <li class="page-item <?= $link["active"] ? "active" : "" ?>">
      <a class="page-link" href="<?= $link["uri"] ?>"><?= $link["title"] ?></a>
    </li>
  <?php endforeach ?>

  <?php if ($pager->getLastPageNumber() < $pager->getPageCount()) : ?>
    <li class="page-item disabled"><span class="page-link">…</span></li>
    <li class="page-item">
      <a class="page-link" href="<?= $pager->getLast() ?>"><?= $pager->getPageCount() ?></a>
    </li>
  <?php endif ?>

  <li class="page-item <?= $pager->hasNext() ? "" : "disabled" ?>">
    <a class="page-link" href="<?= $pager->hasNext() ? $pager->getNext() : "#" ?>" aria-label="Page suivante">
      <i class="ti ti-chevron-right"></i>
    </a>
  </li>
</ul>
