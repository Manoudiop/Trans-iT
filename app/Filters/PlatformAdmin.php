<?php

namespace App\Filters;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Réserve la console d'exploitation aux administrateurs de plateforme.
 *
 * Un 404 plutôt qu'un 403: l'existence de la console n'a pas à être révélée
 * aux utilisateurs des agences.
 *
 * Le drapeau est relu en base à chaque requête et non pris dans la session:
 * retirer les droits à quelqu'un doit prendre effet immédiatement, sans
 * attendre l'expiration de sa session.
 */
class PlatformAdmin implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $id = session()->userData["id"] ?? null;

        if ($id === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        $row = db_connect()->table("users")
            ->select("is_platform_admin")
            ->where("id", $id)
            ->get()
            ->getRowArray();

        if ($row === null or !$row["is_platform_admin"]) {
            throw PageNotFoundException::forPageNotFound();
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
