<?php
namespace FYS\App\Controllers\Mail;

use FYS\App\Services\EmailSender;

class NotificationController {
    
    private $sender;

    public function __construct() {
        // Podrías pasar true/false aquí para debug
        $this->sender = new EmailSender();
    }

    /**
     * Renderiza una plantilla PHP con variables
     */
    private function renderTemplate($templatePath, $data) {
        if (!file_exists($templatePath)) {
            throw new \Exception("Plantilla no encontrada: $templatePath");
        }
        
        extract($data);
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }

    public function enviarBienvenidaQR($destinatario, $nombre, $codigo, $rutaQr) {
        
        // 1. Preparar el HTML
        $templatePath = FYS_DIR . '/src/mail/Templates/welcome_qr.php';
        $htmlBody = $this->renderTemplate($templatePath, [
            'nombre' => $nombre,
            'codigo' => $codigo
        ]);

        // 2. Definir adjuntos e imágenes embebidas
        // Si quieres que el QR vaya como archivo adjunto descargable:
        $attachments = [
            $rutaQr => 'codigo_qr.png'
        ];

        // Si quieres que el QR se vea DENTRO del HTML (cid):
        $embedded = [
            $rutaQr => 'codigo_qr' // 'codigo_qr' coincide con src='cid:codigo_qr' en el HTML
        ];

        // 3. Enviar usando el servicio
        return $this->sender->send(
            $destinatario,
            $nombre,
            'Bienvenido - Tu código de acceso',
            $htmlBody,
            $attachments,
            $embedded
        );
    }
}
