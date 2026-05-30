<?php

namespace App\Console\Commands;

use App\Enums\DocumentTypeEnum;
use App\Models\Document;
use App\Services\FcmNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendDocumentExpiryAlerts extends Command
{
    protected $signature   = 'chocapp:document-expiry-alerts';
    protected $description = 'Enviar alertas push de documentos próximos a vencer';

    public function __construct(private readonly FcmNotificationService $fcm)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $documents = Document::with('vehicle.user')
            ->expiringSoon(30)
            ->get();

        $count = 0;
        foreach ($documents as $doc) {
            $user = $doc->vehicle?->user;
            if (!$user) {
                continue;
            }

            $daysLeft  = (int) now()->diffInDays($doc->expiry_date);
            $typeLabel = $doc->type instanceof DocumentTypeEnum
                ? $doc->type->label()
                : $doc->type;

            $this->fcm->send(
                $user,
                "{$typeLabel} próximo a vencer",
                "Tu {$typeLabel} vence en {$daysLeft} días. ¡Renuévalo ya!",
                ['type' => 'DOCUMENT_EXPIRING', 'document_id' => $doc->id]
            );

            $count++;
        }

        Log::channel('daily')->info("ChocApp: Alertas de documentos enviadas: {$count}");
        $this->info("Alertas enviadas: {$count}");

        return Command::SUCCESS;
    }
}
