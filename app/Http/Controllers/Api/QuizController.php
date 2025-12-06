<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerMeta;
use App\Models\CustomerMetaData;
use App\Models\WhatsAppAccount;
use App\Services\WhatsAppGraphService;
use App\Models\QuizResult;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    public function store(Request $request)
    {
        $payload = $request->validate([
            'phone'          => 'required|string',
            'final_score'    => 'nullable|numeric',
            'stage'          => 'nullable|string',
            'quiz_meta_id'   => 'required|integer',
            'completed_at'   => 'nullable|string',
            'answers'        => 'required|array|min:1',
            'answers.*.question_id'      => 'required|integer',
            'answers.*.question_meta_id' => 'required|integer',
            'answers.*.answer_meta_id'   => 'required|integer',
            'answers.*.score'            => 'nullable|numeric',
            'answers.*.question'         => 'nullable|string',
            'answers.*.answer'           => 'nullable|string',
            'answers.*.comment'          => 'nullable|string',
        ]);

        $phone = $this->cleanPhone($payload['phone']);
        if (!$phone) {
            return response()->json(['error' => 'Invalid phone'], 422);
        }

        $customer = Customer::where('phone', $phone)
            ->orWhere('phone2', $phone)
            ->first();

        if (!$customer) {
            $customer = new Customer();
            $customer->phone = $phone;
            $customer->phone2 = $phone;
            $customer->status_id = 1;
            $customer->notes = '#quiz-escalable';
            $customer->save();
        }

        // If stage is missing, derive it from score using defined ranges.
        $payload['stage'] = $payload['stage'] ?? $this->stageFromScore($payload['final_score']);

        $quizResult = null;

        DB::transaction(function () use ($customer, $payload, &$quizResult) {
            // Prepare feedback/lookups from existing meta catalog
            $answerMetaLookups = $this->buildMetaLookups($payload['answers']);

            // Save quiz level summary
            $this->saveMeta(
                $customer->id,
                $payload['quiz_meta_id'],
                [
                    'final_score'  => $payload['final_score'] ?? null,
                    'stage'        => $payload['stage'] ?? null,
                    'completed_at' => $payload['completed_at'] ?? null,
                ]
            );

            foreach ($payload['answers'] as $answer) {
                $this->saveMeta(
                    $customer->id,
                    $answer['question_meta_id'],
                    [
                        'question_id'    => $answer['question_id'],
                        'answer_meta_id' => $answer['answer_meta_id'],
                        'score'          => $answer['score'] ?? null,
                    ]
                );
            }

            // Save result snapshot for public link
            $quizResult = QuizResult::create([
                'slug' => $this->generateUniqueSlug($customer->name),
                'customer_id' => $customer->id,
                'quiz_meta_id' => $payload['quiz_meta_id'],
                'name' => $customer->name,
                'stage' => $payload['stage'] ?? null,
                'final_score' => $payload['final_score'] ?? null,
                'completed_at' => $payload['completed_at'] ?? null,
                'answers' => $this->enrichAnswersWithMeta($payload['answers'], $answerMetaLookups),
            ]);
        });

        $this->sendWhatsAppConfirmation($customer, $payload, $quizResult);

        return response()->json([
            'customer_id' => $customer->id,
            'saved'       => true,
            'slug'        => $quizResult?->slug,
        ], 201);
    }

    private function saveMeta(int $customerId, int $metaId, $value): void
    {
        if ($metaId === 0 || $value === null) {
            return;
        }

        $meta = new CustomerMeta();
        $meta->customer_id = $customerId;
        $meta->meta_data_id = $metaId;
        $meta->value = is_array($value) ? json_encode($value) : $value;
        $meta->save();
    }

    private function cleanPhone(?string $phone): ?string
    {
        if (!$phone) {
            return null;
        }

        $cleaned = preg_replace('/\D/', '', $phone);
        return $cleaned ?: null;
    }

    private function sendWhatsAppConfirmation(Customer $customer, array $payload, ?QuizResult $quizResult): void
    {
        try {
            $account = WhatsAppAccount::where('is_default', true)->first();
            if (!$account) {
                Log::warning('WA quiz confirmation skipped: no default account configured');
                return;
            }

            $phone = $this->cleanPhone($customer->phone ?? $customer->phone2);
            if (!$phone) {
                Log::warning('WA quiz confirmation skipped: customer phone missing', ['customer_id' => $customer->id]);
                return;
            }

            $name = $customer->name ?: 'allí';
            $stage = $payload['stage'] ?? 'En evaluación';
            $score = $payload['final_score'] ?? '0';
            // Use quiz result slug endpoint so the message points to the generated result.
            $recommendationLink = $quizResult
                ? $this->buildResultLink($quizResult->slug)
                : 'https://maquiempanadas.com';

            $extra = $this->buildInvitationText($stage);

            // WhatsApp template params cannot contain newlines/tabs or 4+ consecutive spaces.
            $params = [
                $this->sanitizeTemplateParam($name),
                $this->sanitizeTemplateParam($stage),
                $this->sanitizeTemplateParam("{$score}"),
                $this->sanitizeTemplateParam($recommendationLink),
                $this->sanitizeTemplateParam($extra),
            ];

            $components = [
                [
                    'type' => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => $params[0]],
                        ['type' => 'text', 'text' => $params[1]],
                        ['type' => 'text', 'text' => $params[2]],
                        ['type' => 'text', 'text' => $params[3]],
                        ['type' => 'text', 'text' => $params[4] ?: ''],
                    ],
                ],
            ];

            Log::info('WA quiz confirmation sending', [
                'customer_id' => $customer->id,
                'phone' => $phone,
                'stage' => $stage,
                'score' => $score,
                'recommendation_link' => $recommendationLink,
                'extra_present' => (bool)$extra,
                'slug' => $quizResult?->slug,
            ]);

            app(WhatsAppGraphService::class)->sendTemplate($account, $phone, 'resultado_quiz', 'es', $components);

            Log::info('WA quiz confirmation sent', [
                'customer_id' => $customer->id,
                'phone' => $phone,
                'template' => 'resultado_quiz',
                'slug' => $quizResult?->slug,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error sending WhatsApp confirmation', [
                'customer_id' => $customer->id ?? null,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function buildResultLink(string $slug): string
    {
        // Prefer explicit public/report URL, falling back to frontend path.
        $base = rtrim(
            env('QUIZ_RESULT_PUBLIC_BASE')
                ?: env('QUIZ_RESULT_BASE_URL')
                ?: 'https://maquiempanadas.com/diagnostico',
            '/'
        );
        return "{$base}/{$slug}";
    }

    private function sanitizeTemplateParam(?string $text): string
    {
        if ($text === null) {
            return '';
        }

        $clean = preg_replace('/[\\t\\r\\n]+/', ' ', $text);
        $clean = preg_replace('/ {2,}/', ' ', $clean);

        return trim($clean ?? '');
    }

    private function stageFromScore($score): ?string
    {
        if ($score === null) {
            return null;
        }

        $score = (float)$score;

        if ($score >= 0 && $score <= 20) {
            return 'Inicio Manual';
        }
        if ($score >= 21 && $score <= 40) {
            return 'Crecimiento Artesanal';
        }
        if ($score >= 41 && $score <= 60) {
            return 'Fábrica en Orden';
        }
        if ($score >= 61 && $score <= 80) {
            return 'Producción Escalable';
        }
        if ($score >= 81 && $score <= 90) {
            return 'Expansión Mayorista';
        }
        if ($score >= 91 && $score <= 100) {
            return 'Marca que Trasciende';
        }

        return null;
    }

    private function buildInvitationText(?string $stage): string
    {
        $eligibleStages = ['Fábrica en Orden', 'Producción Escalable'];
        $now = Carbon::now(config('app.timezone', 'UTC'));
        $sessionDate = Carbon::create($now->year, 12, 11, 10, 0, 0, $now->timezone);

        $isStageEligible = $stage && in_array($stage, $eligibleStages, true);
        $isDateEligible = $now->lt($sessionDate);

        $groupInvite = "También podés unirte al grupo de empanaderos de Maquiempanadas para tips y dudas:\n🔗 https://chat.whatsapp.com/CPVD7CuPIB59nB2tvYwTJj?mode=hqrt3";

        if (!$isStageEligible || !$isDateEligible) {
            return "Gracias por completar tu diagnóstico; seguimos en contacto con más recomendaciones para tu etapa.\n{$groupInvite}";
        }

        $sessionText = "Además, por tu resultado estás en una etapa lista para aumentar producción sin contratar más personal.\nEsta semana habrá una sesión privada con el fundador donde explica cómo hacerlo paso a paso. Si querés sumarte, respondé CRECER y este es el acceso:\n🔗 https://us02web.zoom.us/j/87076597059?pwd=MtQYDGkN1uTCXGJGduWxTYxo2aQxJI.1";

        return "{$sessionText}\n{$groupInvite}";
    }

    private function formatAnswers($answers): array
    {
        if (!is_array($answers)) {
            return [];
        }

        return array_map(function ($item) {
            $questionId = $item['question_id'] ?? null;

            return [
                'question' => $item['question'] ?? $item['question_text'] ?? ($questionId ? "Pregunta #{$questionId}" : ''),
                'answer' => $item['answer'] ?? $item['answer_text'] ?? ($item['answer_meta_id'] ?? ''),
                'score' => $item['score'] ?? null,
                'comment' => $item['comment'] ?? '',
            ];
        }, $answers);
    }

    private function buildMetaLookups(array $answers): array
    {
        $answerMetaIds = array_unique(array_filter(array_column($answers, 'answer_meta_id')));
        $questionMetaIds = array_unique(array_filter(array_column($answers, 'question_meta_id')));

        $answerMeta = [];
        $questionMeta = [];
        $commentsByParent = [];
        $parentQuestionIds = [];

        if ($answerMetaIds) {
            $answerMeta = CustomerMetaData::whereIn('id', $answerMetaIds)->get()->keyBy('id');
            $parentQuestionIds = $answerMeta->pluck('parent_id')->filter()->unique()->values()->all();

            $commentsByParent = CustomerMetaData::whereIn('parent_id', $answerMetaIds)
                ->get()
                ->groupBy('parent_id');
        }

        $allQuestionIds = array_values(array_unique(array_merge($questionMetaIds, $parentQuestionIds)));
        if ($allQuestionIds) {
            $questionMeta = CustomerMetaData::whereIn('id', $allQuestionIds)->get()->keyBy('id');
        }

        return [
            'answers' => $answerMeta,
            'questions' => $questionMeta,
            'comments' => $commentsByParent,
        ];
    }

    private function enrichAnswersWithMeta(array $answers, array $lookups): array
    {
        return array_map(function ($item) use ($lookups) {
            $answerMetaId = $item['answer_meta_id'] ?? null;
            $questionMetaId = $item['question_meta_id'] ?? null;

            $answerMeta = ($answerMetaId && isset($lookups['answers'][$answerMetaId])) ? $lookups['answers'][$answerMetaId] : null;
            $questionMeta = ($questionMetaId && isset($lookups['questions'][$questionMetaId])) ? $lookups['questions'][$questionMetaId] : null;
            if (!$questionMeta && $answerMeta && $answerMeta->parent_id && isset($lookups['questions'][$answerMeta->parent_id])) {
                $questionMeta = $lookups['questions'][$answerMeta->parent_id];
                $questionMetaId = $answerMeta->parent_id;
                $item['question_meta_id'] = $questionMetaId;
            }
            $commentMeta = ($answerMetaId && isset($lookups['comments'][$answerMetaId]))
                ? $lookups['comments'][$answerMetaId]->first()
                : null;

            $item['question'] = $item['question']
                ?? $item['question_text']
                ?? ($questionMeta->value ?? ($questionMetaId ? "Pregunta #{$questionMetaId}" : ''));

            $item['answer'] = $item['answer']
                ?? $item['answer_text']
                ?? ($answerMeta->value ?? ($answerMetaId ?? ''));

            $item['comment'] = $item['comment']
                ?? ($commentMeta->value ?? '');

            return $item;
        }, $answers);
    }

    public function showResult(string $slug)
    {
        $result = QuizResult::where('slug', $slug)->first();
        if (!$result) {
            return $this->corsResponse(['error' => 'Not found'], 404);
        }

        $customerName = $result->name ?? $result->customer?->name;
        $customerPhone = $result->customer?->phone ?? $result->customer?->phone2;
        $rawAnswers = is_array($result->answers) ? $result->answers : [];
        $lookups = $this->buildMetaLookups($rawAnswers);
        $enrichedAnswers = $this->enrichAnswersWithMeta($rawAnswers, $lookups);

        return $this->corsResponse([
            'slug' => $result->slug,
            'customer_id' => $result->customer_id,
            'name' => $customerName,
            'phone' => $customerPhone,
            'stage' => $result->stage,
            'final_score' => $result->final_score,
            'completed_at' => $result->completed_at,
            'answers' => $this->formatAnswers($enrichedAnswers),
        ]);
    }

    private function corsResponse($data, int $status = 200)
    {
        return response()
            ->json($data, $status)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }

    private function generateUniqueSlug(?string $name): string
    {
        $base = Str::slug($name ?: 'cliente');
        if (strlen($base) < 3) {
            $base = 'cliente';
        }

        do {
            $slug = $base . '-' . Str::random(6);
        } while (QuizResult::where('slug', $slug)->exists());

        return $slug;
    }
}
