<?php
class AIConfig {
    public static function groqEndpoint(): string {
        return 'Ai-API-KEY-YOUR';
    }
    public static function groqModel(): string {
        return 'llama-3.1-8b-instant';
    }
    public static function groqApiKey(): string {
        // Local demo: hardcode your Groq token below and keep it private
        // Example: return 'gsk_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
        return 'Ai-API-KEY-YOUR';
    }
    public static function temperature(): float {
        return 0.4;
    }
    public static function timeoutSeconds(): int {
        return 12;
    }
}
?>
