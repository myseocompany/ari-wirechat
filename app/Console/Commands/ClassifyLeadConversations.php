<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Services\LeadClassifier\LeadConversationClassifier;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Namu\WireChat\Models\Message;

class ClassifyLeadConversations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'leads:classify
        {--date= : Fecha base en formato Y-m-d (por defecto: ayer)}
        {--days=1 : Cantidad de dias a procesar desde --date}
        {--limit= : Limite de conversaciones a procesar}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clasifica conversaciones de clientes y guarda tipificacion por conversation_id.';

    public function __construct(
        private readonly LeadConversationClassifier $classifier
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $baseDate = $this->option('date')
            ? Carbon::createFromFormat('Y-m-d', (string) $this->option('date'))
            : Carbon::yesterday();

        $days = max(1, (int) $this->option('days'));

        $fromDate = $baseDate->copy()->startOfDay();
        $toDate = $baseDate->copy()->addDays($days - 1)->endOfDay();

        $this->info('Clasificando conversaciones de clientes.');
        $this->line("Rango: {$fromDate->toDateTimeString()} -> {$toDate->toDateTimeString()}");

        $conversationIds = $this->getConversationIds($fromDate, $toDate);

        $limitOption = $this->option('limit');
        if ($limitOption !== null && $limitOption !== '') {
            $limit = max(1, (int) $limitOption);
            $conversationIds = $conversationIds->take($limit)->values();
            $this->line("Aplicando limite: {$limit}");
        }

        if ($conversationIds->isEmpty()) {
            $this->warn('No se encontraron conversaciones con mensajes del cliente en el rango dado.');

            return self::SUCCESS;
        }

        $processed = 0;
        $skipped = 0;

        $progressBar = $this->output->createProgressBar($conversationIds->count());
        $progressBar->start();

        foreach ($conversationIds as $conversationId) {
            $result = $this->classifier->classify((int) $conversationId);

            if ($result === null) {
                $skipped++;
            } else {
                $processed++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info("Procesadas: {$processed} · Omitidas: {$skipped} · Total: ".$conversationIds->count());

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, int|string>
     */
    private function getConversationIds(Carbon $fromDate, Carbon $toDate): Collection
    {
        $customerMorph = (new Customer)->getMorphClass();
        $customerClass = Customer::class;

        return Message::query()
            ->select('conversation_id')
            ->whereIn('sendable_type', [$customerMorph, $customerClass])
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->whereNotNull('conversation_id')
            ->distinct()
            ->orderBy('conversation_id')
            ->pluck('conversation_id');
    }
}
