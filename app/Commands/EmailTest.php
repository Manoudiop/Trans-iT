<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Email as EmailConfig;

/**
 * Vérifie la configuration SMTP en envoyant un message réel.
 *
 * Sans cette commande, la seule façon d'éprouver le courrier sortant serait
 * de demander une réinitialisation de mot de passe et d'attendre: on ne
 * saurait pas distinguer un identifiant SMTP erroné d'une adresse mal
 * saisie, ni lire la raison du refus renvoyée par le serveur.
 *
 * Le mot de passe n'est jamais affiché, seulement sa présence.
 */
class EmailTest extends BaseCommand
{
    protected $group = "Trans-iT";
    protected $name = "email:test";
    protected $description = "Envoie un message de contrôle pour vérifier la configuration SMTP.";
    protected $usage = "email:test <destinataire>";
    protected $arguments = [
        "destinataire" => "Adresse qui doit recevoir le message de contrôle.",
    ];

    public function run(array $params)
    {
        // Pas de prompt interactif: la commande tourne aussi depuis un script
        // de deploiement, ou l'entree standard n'est pas attachee.
        $destinataire = $params[0] ?? "";

        if ($destinataire === "") {
            CLI::error("Indiquez l'adresse de destination: php spark email:test vous@exemple.sn");

            return EXIT_ERROR;
        }

        if (!filter_var($destinataire, FILTER_VALIDATE_EMAIL)) {
            CLI::error("Adresse invalide: " . $destinataire);

            return EXIT_ERROR;
        }

        $config = config(EmailConfig::class);

        CLI::write("Configuration lue:", "yellow");
        $this->ligne("protocol", $config->protocol);
        $this->ligne("fromEmail", $config->fromEmail);
        $this->ligne("fromName", $config->fromName);

        if ($config->protocol === "smtp") {
            $this->ligne("SMTPHost", $config->SMTPHost);
            $this->ligne("SMTPPort", (string) $config->SMTPPort);
            $this->ligne("SMTPCrypto", $config->SMTPCrypto);
            $this->ligne("SMTPUser", $config->SMTPUser);
            // Jamais la valeur: cette sortie finit dans des journaux et des
            // copies d'écran.
            $this->ligne("SMTPPass", $config->SMTPPass === "" ? "(vide)" : "(renseigné)");
        }

        CLI::newLine();

        if ($config->fromEmail === "") {
            CLI::error("email.fromEmail n'est pas renseigné: rien ne peut partir.");
            CLI::write("Renseignez les clés email.* dans .env (voir le fichier env).");

            return EXIT_ERROR;
        }

        if ($config->protocol === "smtp" and $config->SMTPHost === "") {
            CLI::error("email.protocol vaut smtp mais email.SMTPHost est vide.");

            return EXIT_ERROR;
        }

        CLI::write("Envoi vers " . $destinataire . "...", "yellow");

        $courriel = service("email");
        $courriel->setFrom($config->fromEmail, $config->fromName ?: "Trans It!");
        $courriel->setTo($destinataire);
        $courriel->setSubject("Trans It! — message de contrôle");
        $courriel->setMessage(
            "Ce message confirme que l'envoi de courriels fonctionne.\n\n"
            . "Émis le " . date("d/m/Y à H:i") . " depuis " . ENVIRONMENT . ".\n"
            . "Le parcours « mot de passe oublié » peut donc aboutir.\n"
        );

        if (!$courriel->send(false)) {
            CLI::error("Échec de l'envoi.");
            CLI::newLine();
            // Le détail du dialogue SMTP: c'est lui qui nomme la cause réelle,
            // authentification refusée, port filtré ou certificat rejeté.
            CLI::write($courriel->printDebugger(["headers"]));

            return EXIT_ERROR;
        }

        CLI::write("Message accepté par le serveur. Vérifiez la boîte de réception.", "green");

        return EXIT_SUCCESS;
    }

    private function ligne(string $cle, string $valeur): void
    {
        CLI::write("  " . str_pad($cle, 12) . " " . ($valeur === "" ? "(vide)" : $valeur));
    }
}
