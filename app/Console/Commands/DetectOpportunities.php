<?php

namespace App\Console\Commands;

use App\Services\Opportunities\OpportunityDetectorService;
use Illuminate\Console\Command;

class DetectOpportunities extends Command
{
    protected $signature = 'opportunities:detect
        {--from_date= : Fecha inicial YYYY-MM-DD}
        {--to_date= : Fecha final YYYY-MM-DD}
        {--message_search= : Texto a buscar en mensajes}
        {--action_note_search= : Texto a buscar en acciones}
        {--priority= : high, medium o low}
        {--maker= : unknown, project, makes u other}
        {--production_min= : Produccion minima diaria estimada}
        {--unattended : Solo clientes con mensaje posterior a la ultima accion humana}
        {--limit=100 : Maximo de prospectos a analizar}';

    protected $description = 'Detecta y prioriza oportunidades comerciales desde conversaciones de clientes.';

    public function handle(OpportunityDetectorService $detector): int
    {
        $filters = [
            'from_date' => $this->option('from_date'),
            'to_date' => $this->option('to_date'),
            'message_search' => $this->option('message_search'),
            'action_note_search' => $this->option('action_note_search'),
            'priority' => $this->option('priority'),
            'maker' => $this->option('maker'),
            'production_min' => $this->option('production_min'),
            'unattended' => $this->option('unattended'),
            'limit' => $this->option('limit'),
        ];

        $result = $detector->analyze(array_filter($filters, fn ($value) => $value !== null && $value !== ''), 100);
        $rows = collect($result['model']->items())->take(25);

        $this->info(sprintf(
            'Oportunidades %s a %s: %d analizados de %d candidatos, %d alta, %d media, %d sin atender.',
            $result['fromDate']->toDateString(),
            $result['toDate']->toDateString(),
            $result['summary']['analyzed'],
            $result['summary']['candidate_total'],
            $result['summary']['high'],
            $result['summary']['medium'],
            $result['summary']['unattended']
        ));

        $this->table(
            ['Score', 'Prioridad', 'Cliente', 'Telefono', 'Produce', 'Emp/dia', 'Estado', 'Asesor', 'Motivos'],
            $rows->map(fn ($row) => [
                $row->opportunity_score,
                $row->priority_label,
                mb_strimwidth((string) $row->name, 0, 32, '...'),
                preg_replace('/\D+/', '', (string) $row->phone),
                $row->production_label,
                $row->estimated_daily_empanadas ? number_format($row->estimated_daily_empanadas) : '-',
                $row->status_name ?? 'Sin estado',
                $row->user_name ?? 'Sin asignar',
                mb_strimwidth(implode('; ', $row->opportunity_reasons), 0, 90, '...'),
            ])->all()
        );

        return self::SUCCESS;
    }
}
