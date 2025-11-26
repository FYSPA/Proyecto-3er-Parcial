<?php

namespace FYS\Helpers;

/**
 * DTO para representar un error de aplicación
 */
class Error {
    private string $message;
    private int $code;
    private ?array $context;

    public function __construct(string $message, int $code = 0, ?array $context = []) {
        $this->message = $message;
        $this->code = $code;
        $this->context = $context;
    }

    /** Getters */
    public function getMessage(): string {
        return $this->message;
    }

    public function getCode(): int {
        return $this->code;
    }

    public function getContext(): array {
        return $this->context ?? [];
    }

    /** Convierte a array (útil para devolver en JSON) */
    public function toArray(): array {
        return [
            'error'   => true,
            'message' => $this->message,
            'code'    => $this->code,
            'context' => $this->context,
        ];
    }

    /** Convierte a JSON directamente */
    public function toJson(): string {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE);
    }
}
