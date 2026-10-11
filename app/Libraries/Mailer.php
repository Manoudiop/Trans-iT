<?php

namespace App\Libraries;

use Config\Email as EmailConfig;

/**
 * Envoi de courriel, avec un repli explicite hors production.
 *
 * Le service n'a pas de serveur SMTP en développement. Plutôt que de faire
 * croire à un envoi réussi, le message est alors écrit dans
 * writable/mail/ — on peut l'ouvrir et suivre le lien, ce qui rend le
 * parcours vérifiable de bout en bout.
 *
 * En production ce repli est refusé: un courriel de réinitialisation qui
 * atterrit dans un fichier au lieu de la boîte du client est une panne
 * silencieuse, pas une commodité.
 */
class Mailer
{
    /**
     * @return array{0: bool, 1: string} Succès, et message expliquant l'échec.
     */
    public function send(string $destinataire, string $sujet, string $corps): array
    {
        $config = config(EmailConfig::class);

        if ($this->estConfigure($config)) {
            $courriel = service("email");
            $courriel->setFrom($config->fromEmail, $config->fromName ?: "Trans It!");
            $courriel->setTo($destinataire);
            $courriel->setSubject($sujet);
            $courriel->setMessage($corps);

            if ($courriel->send(false)) {
                return [true, ""];
            }

            log_message("error", "Envoi de courriel échoué: " . $courriel->printDebugger(["headers"]));

            return [false, "L'envoi du courriel a échoué."];
        }

        if (ENVIRONMENT === "production") {
            log_message(
                "critical",
                "Aucun serveur SMTP configuré: le courriel destiné à " . $destinataire . " n'a pas été envoyé."
            );

            return [false, "L'envoi de courriels n'est pas configuré sur ce serveur."];
        }

        $this->ecrireSurDisque($destinataire, $sujet, $corps);

        return [true, ""];
    }

    /**
     * Un expéditeur et, en SMTP, un hôte: sans cela rien ne peut partir.
     */
    private function estConfigure(EmailConfig $config): bool
    {
        if ($config->fromEmail === "") {
            return false;
        }

        return $config->protocol !== "smtp" or $config->SMTPHost !== "";
    }

    private function ecrireSurDisque(string $destinataire, string $sujet, string $corps): void
    {
        $dossier = WRITEPATH . "mail";

        if (!is_dir($dossier)) {
            mkdir($dossier, 0777, true);
        }

        $nom = date("Ymd-His") . "-" . preg_replace("/[^a-zA-Z0-9._-]/", "_", $destinataire) . ".txt";

        file_put_contents(
            $dossier . DIRECTORY_SEPARATOR . $nom,
            "À: " . $destinataire . "\nSujet: " . $sujet . "\n\n" . $corps . "\n"
        );

        log_message(
            "warning",
            "Aucun serveur SMTP configuré: courriel écrit dans writable/mail/" . $nom
        );
    }
}
