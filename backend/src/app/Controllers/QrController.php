<?php
namespace FYS\App\Controllers;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class QrController {
    
     /**
     * Genera un archivo QR y devuelve la ruta absoluta del archivo.
     * @param string $codigo_acceso El token para la URL.
     * @param string $newId ID único para el nombre del archivo.
     * @return string Ruta absoluta del archivo generado.
     * @throws \Exception Si falla la generación.
     */
    public function genQRCode(string $codigo_acceso, string $newId, string $correo): string {
        // Definir directorio
        $qrDir = FYS_DIR . '/qrcodes/';

        if (!is_dir($qrDir)) {
            if (!@mkdir($qrDir, 0755, true)) {
                $error = error_get_last();
                if (!is_dir($qrDir)) {
                    throw new \Exception("No se pudo crear el directorio de QR: $qrDir. Motivo: " . ($error['message'] ?? 'Desconocido'));
                }
            }
        }
        
        $frontendUrl = rtrim(FYS_PUBLIC_FRONTEND_URL ?? "http://localhost.com", '/');
        $qrUrl = "{$frontendUrl}/verificacion?codigo={$codigo_acceso}&correo={$correo}";

        
        $qrFilename = $qrDir . "qr_{$newId}.png";
        $options = new QROptions([
            'version'      => 5,
            'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel'     => QRCode::ECC_L,
            'scale'        => 6,
            'imageBase64'  => false,
        ]);

        try {
            (new QRCode($options))->render($qrUrl, $qrFilename);
        } catch (\Throwable $e) {
            throw new \Exception("Error generando QR con Chillerlan: " . $e->getMessage());
        }

        if (!file_exists($qrFilename)) {
            throw new \Exception("El archivo QR no se generó físicamente en: $qrFilename");
        }

        return $qrFilename;
    }
}
