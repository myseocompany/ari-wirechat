<?php

namespace App\Console\Commands;

use App\Models\ActionTranscription;
use Illuminate\Console\Command;

class ActionTranscriptionStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transcription:status {transcription_id : ID de la transcripción}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Muestra el estado y progreso de una transcripción';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $id = (int) $this->argument('transcription_id');
        $transcription = ActionTranscription::with('action')->find($id);

        if (! $transcription) {
            $this->error('Transcripción no encontrada.');

            return self::FAILURE;
        }

        $this->info('Transcripción #'.$transcription->id);
        $this->line('Action ID: '.$transcription->action_id);
        $this->line('Status: '.$transcription->status);
        $this->line('Step: '.($transcription->progress_step ?? '-'));
        $this->line('Message: '.($transcription->progress_message ?? '-'));
        $this->line('Percent: '.($transcription->progress_percent !== null ? $transcription->progress_percent.'%' : '-'));
        $this->line('Updated: '.optional($transcription->updated_at)->toDateTimeString());

        if ($transcription->error_message) {
            $this->warn('Error: '.$transcription->error_message);
        }

        return self::SUCCESS;
    }
}
