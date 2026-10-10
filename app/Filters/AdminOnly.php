<?php

namespace App\Filters;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Réserve une page à l'administrateur de l'agence.
 *
 * Distinct de UserManagement, qui parle de droits sur les comptes: ici il
 * s'agit des paramètres de l'agence elle-même, et le message doit le dire.
 */
class AdminOnly implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (session()->userData["profile"] !== "ADMIN") {
            throw new PageNotFoundException(
                "Seul un administrateur peut consulter les paramètres de l'agence.",
                403
            );
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
