<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', "Users::index");
$routes->post('/', 'Users::login');

//inscription en libre-service, hors authentification
$routes->get('inscription', 'Signup::form');
$routes->post('inscription', 'Signup::create');
$routes->group('', ['filter' => 'auth'], function ($routes) {
  $routes->get('tableau-de-bord', 'Users::dashboard');
  $routes->post('edit-password', 'Users::editPwd');

  //user management
  $routes->group('utilisateurs', ['filter' => 'userManagement'], function ($routes) {
    $routes->get('/', 'Users::list');
    $routes->post('modifier', 'Users::save');
    $routes->get('modifier/(:num)', 'Users::editPage/$1');
    $routes->post('supprimer', 'Users::delete');
    $routes->post('ajouter', 'Users::save');
    $routes->get('ajouter', 'Users::addPage');
  });

  //client management
  $routes->group('clients', ['filter' => 'canInvoice'], function ($routes) {
    $routes->get('/', 'Clients::index');
    $routes->post('supprimer', 'Clients::delete');
    $routes->get('modifier/(:segment)', 'Clients::editPage/$1');
    $routes->post('modifier', 'Clients::save');
    $routes->get('ajouter', 'Clients::addPage');
    $routes->post('ajouter', 'Clients::save');
  });

  //transit folder management
  $routes->group('dossiers', function ($routes) {
    $routes->get('/', 'TransitFolders::index');
    $routes->get('ajouter', 'TransitFolders::addPage');
    $routes->post('ajouter', 'TransitFolders::add');
    $routes->get('modifier/(:num)', 'TransitFolders::editPage/$1');
    $routes->post('modifier', 'TransitFolders::edit');
    $routes->post('ajouter-colis', 'TransitFolders::addItem');
    $routes->post('supprimer-colis', 'TransitFolders::deleteItem');
    $routes->get('information/(:num)', 'TransitFolders::info/$1');
    $routes->get('imprimer/(:num)', 'TransitFolders::print/$1');
    $routes->get('supprimer/(:num)', 'TransitFolders::deletePage/$1');
    $routes->post('supprimer/(:num)', 'TransitFolders::delete/$1');
    $routes->post('ajouter-fichier', 'TransitFolders::addFile');
    $routes->post('supprimer-fichier', 'TransitFolders::deleteFile');
    $routes->get('fichier/(:num)', 'TransitFolders::file/$1');
    $routes->get('suivi', 'TransitFolders::tracking');
    $routes->get('corbeille', 'TransitFolders::trash');
    $routes->post('restaurer', 'TransitFolders::restore');
  });

  //invoice management
  $routes->group('factures', ['filter' => 'canInvoice'], function ($routes) {
    $routes->get('/', 'Invoices::index');
    $routes->get('non-factures', 'Invoices::notInvoiced');
    $routes->get('encours', 'Invoices::outstanding');
    $routes->post('reglements/ajouter', 'Settlements::add');
    $routes->post('reglements/supprimer', 'Settlements::delete');
    $routes->get('reglements/(:num)', 'Settlements::index/$1');
    $routes->get('facturer/(:num)', 'Invoices::invoicePage/$1');
    $routes->post('facturer/(:num)', 'Invoices::invoice/$1');
    $routes->get('modifier/(:num)', 'Invoices::editPage/$1');
    $routes->post('modifier/(:num)', 'Invoices::edit/$1');
    $routes->get('supprimer/(:num)', 'Invoices::deletePage/$1');
    $routes->post('supprimer/(:num)', 'Invoices::delete/$1');
    $routes->get('imprimer/(:num)', 'Invoices::print/$1');
  });

  //console d'exploitation de la plateforme
  $routes->group('console', ['filter' => 'platformAdmin'], function ($routes) {
    $routes->get('/', 'Console::index');
    $routes->post('suspendre', 'Console::toggle');
    $routes->post('offre', 'Console::changePlan');
  });

  //rapports
  $routes->group('rapports', function ($routes) {
    $routes->get("/", "Reports::index");
    $routes->get("dossiers", "Reports::transitFolders");
    $routes->group('', ['filter' => 'canInvoice'], function ($routes) {
      $routes->get("factures", "Reports::transitInvoices");
    });
  });
});
$routes->get('deconnexion', 'Users::logout');
