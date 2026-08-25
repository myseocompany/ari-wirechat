<?php

use App\Services\CustomerCallBriefingGenerator;
use Tests\TestCase;

uses(TestCase::class);

function validCustomerCallBriefing(): array
{
    return [
        'summary' => 'El cliente solicitó una cotización para ampliar su producción.',
        'known_facts' => [
            ['text' => 'Solicitó una cotización.', 'occurred_at' => '2026-08-20T12:00:00Z', 'source' => 'action:10'],
        ],
        'conflicting_facts' => [],
        'avoid_asking' => [],
        'suggested_opening' => 'El 20 de agosto solicitaste una cotización. Quisiera retomar ese avance.',
        'next_question' => '¿Qué información falta para evaluar la cotización?',
    ];
}

it('accepts a briefing that matches the LLM contract', function () {
    $briefing = app(CustomerCallBriefingGenerator::class)->validateResponse(validCustomerCallBriefing());

    expect($briefing['summary'])->toBe('El cliente solicitó una cotización para ampliar su producción.')
        ->and($briefing['known_facts'])->toHaveCount(1);
});

it('rejects unknown contract fields and invalid fact dates', function () {
    $generator = app(CustomerCallBriefingGenerator::class);
    $withExtraField = validCustomerCallBriefing();
    $withExtraField['unexpected'] = true;

    expect(fn () => $generator->validateResponse($withExtraField))
        ->toThrow(\RuntimeException::class, 'invalid_llm_response');

    $withInvalidDate = validCustomerCallBriefing();
    $withInvalidDate['known_facts'][0]['occurred_at'] = '2026-08-20';

    expect(fn () => $generator->validateResponse($withInvalidDate))
        ->toThrow(\RuntimeException::class, 'invalid_llm_response');
});

it('rejects an opening with more than two sentences and multiple questions', function () {
    $generator = app(CustomerCallBriefingGenerator::class);
    $briefing = validCustomerCallBriefing();
    $briefing['suggested_opening'] = 'Primero. Segundo. Tercero.';

    expect(fn () => $generator->validateResponse($briefing))
        ->toThrow(\RuntimeException::class, 'invalid_llm_response');

    $briefing = validCustomerCallBriefing();
    $briefing['next_question'] = '¿Cuál es el volumen? ¿Cuál es el presupuesto?';

    expect(fn () => $generator->validateResponse($briefing))
        ->toThrow(\RuntimeException::class, 'invalid_llm_response');
});
