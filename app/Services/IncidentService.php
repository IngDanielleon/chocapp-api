<?php

namespace App\Services;

use App\DTOs\Incident\CreateIncidentDTO;
use App\Enums\IncidentStatusEnum;
use App\Events\IncidentCreated;
use App\Models\Incident;
use App\Models\User;
use App\Repositories\Contracts\IncidentRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IncidentService
{
    public function __construct(
        private readonly IncidentRepositoryInterface $repository,
        private readonly StorageService              $storageService,
    ) {}

    /**
     * @param  array<array{file: UploadedFile, angle: string}>  $photos
     */
    public function create(CreateIncidentDTO $dto, array $photos, User $user): Incident
    {
        return DB::transaction(function () use ($dto, $photos, $user): Incident {
            $incident = $this->repository->create([
                'user_id'              => $user->id,
                'vehicle_id'           => $dto->vehicleId,
                'title'                => $dto->title,
                'description'          => $dto->description,
                'incident_date'        => $dto->incidentDate,
                'incident_time'        => $dto->incidentTime,
                'location_address'     => $dto->locationAddress,
                'latitude'             => $dto->latitude,
                'longitude'            => $dto->longitude,
                'weather_condition'    => $dto->weatherCondition,
                'road_condition'       => $dto->roadCondition,
                'police_report_number' => $dto->policeReportNumber,
                'status'               => IncidentStatusEnum::REPORTADO->value,
            ]);

            $this->uploadPhotos($incident, $photos);

            event(new IncidentCreated($incident));

            return $incident->load(['photos', 'thirdParties', 'vehicle']);
        });
    }

    /**
     * @param  array<array{file: UploadedFile, angle: string}>  $photos
     */
    public function uploadPhotos(Incident $incident, array $photos): void
    {
        foreach ($photos as $photoData) {
            try {
                $url = $this->storageService->uploadFile(
                    $photoData['file'],
                    "incidents/{$incident->id}/photos"
                );

                $incident->photos()->create([
                    'angle'     => strtoupper($photoData['angle']),
                    'image_url' => $url,
                    'taken_at'  => now(),
                ]);
            } catch (\Throwable $e) {
                Log::error('ChocApp: Error uploading incident photo', [
                    'incident_id' => $incident->id,
                    'angle'       => $photoData['angle'],
                    'error'       => $e->getMessage(),
                ]);
            }
        }
    }

    public function updateStatus(Incident $incident, IncidentStatusEnum $status): Incident
    {
        $incident->update(['status' => $status->value]);
        return $incident->fresh();
    }

    public function deleteWithPhotos(Incident $incident): void
    {
        DB::transaction(function () use ($incident): void {
            foreach ($incident->photos as $photo) {
                $this->storageService->deleteFile($photo->image_url);
            }
            $incident->photos()->delete();
            $incident->thirdParties()->delete();
            $incident->delete();
        });
    }
}
