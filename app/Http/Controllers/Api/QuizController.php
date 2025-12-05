<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            $customer->save();
        }

        DB::transaction(function () use ($customer, $payload) {
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
        });

        return response()->json([
            'customer_id' => $customer->id,
            'saved'       => true,
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
}
