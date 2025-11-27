<?php
namespace FYS\App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use FYS\Core\EnvManager;

class EmailSender {
    private $mail;

    public function __construct($debug = false) {
        $this->mail = new PHPMailer(true);
        $this->setupServer($debug);
    }

    private function setupServer($debug) {
        $this->mail->isSMTP();
        $this->mail->SMTPAuth   = true;
        $this->mail->Host       = EnvManager::get('SMTP_HOST');
        $this->mail->Username   = EnvManager::get('SMTP_USER');
        $this->mail->Password   = EnvManager::get('SMTP_PASS');
        
        $port = EnvManager::get('SMTP_PORT', 465);
        $this->mail->Port = $port;
        $this->mail->SMTPSecure = ($port == 465) ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;

        $this->mail->Timeout = 10;
        
        // Configurar remitente por defecto
        $this->mail->setFrom($this->mail->Username, 'VGS System');

        if ($debug) {
            $this->mail->SMTPDebug = 2;
        }
    }

    /**
     * @param string $to Email destinatario
     * @param string $name Nombre destinatario
     * @param string $subject Asunto
     * @param string $body Contenido HTML
     * @param array $attachments Array ['ruta' => 'nombre']
     * @param array $embeddedImages Array ['ruta' => 'cid']
     */
    public function send($to, $name, $subject, $body, $attachments = [], $embeddedImages = []) {
        try {
            // Limpiar destinatarios previos si se reusa la instancia
            $this->mail->clearAddresses();
            $this->mail->clearAttachments();

            $this->mail->addAddress($to, $name);
            $this->mail->isHTML(true);
            $this->mail->Subject = $subject;
            $this->mail->Body    = $body;
            $this->mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], "\n", $body));

            // Adjuntos normales
            foreach ($attachments as $path => $filename) {
                if (file_exists($path)) {
                    $this->mail->addAttachment($path, $filename);
                }
            }

            // Imágenes embebidas (CID)
            foreach ($embeddedImages as $path => $cid) {
                if (file_exists($path)) {
                    $this->mail->addEmbeddedImage($path, $cid, basename($path));
                }
            }

            $this->mail->send();
            return true;

        } catch (Exception $e) {
            $errorMsg = "Error PHPMailer: " . $this->mail->ErrorInfo;
            throw new \Exception($errorMsg);
        }
    }
}
