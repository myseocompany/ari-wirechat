<?php

namespace App\Services\LeadClassifier;

use App\Models\LeadConversationClassification;

class LeadScoreCalculator
{
    /**
     * @param  array<string, bool|int|null>  $signals
     * @return array{score: int, status: string}
     */
    public function calculate(array $signals): array
    {
        $score = 0;

        $weights = [
            'pide_cita_fabrica' => 70,
            'pide_llamada' => 40,
            'tiene_productos' => 30,
            'volumen_mayor_500' => 30,
            'dolor_operarios' => 12,
            'dolor_tiempo' => 10,
            'dolor_merma_calidad' => 8,
            'apertura_nuevo_punto' => 15,
            'demanda_supera_capacidad' => 15,
            'habla_escalar' => 10,
            'urgencia_alta' => 12,
            'tiene_presupuesto' => 18,
            'pide_cotizacion_o_ficha' => 15,
            'pregunta_pago_logistica' => 10,
            'negocio_activo_explicitado' => 12,
        ];

        foreach ($weights as $key => $weight) {
            if (($signals[$key] ?? false) === true) {
                $score += $weight;
            }
        }

        $soloProyecto = ($signals['solo_proyecto'] ?? false) === true;
        $tieneProductos = ($signals['tiene_productos'] ?? false) === true;

        if ($soloProyecto && ! $tieneProductos) {
            $score -= 20;
        }

        $score = max(0, min(100, $score));

        $status = LeadConversationClassification::STATUS_NO_CALIFICADO;

        if (($signals['pide_cita_fabrica'] ?? false) === true) {
            $status = LeadConversationClassification::STATUS_CALIFICADO;
        } elseif ($score >= 70) {
            $status = LeadConversationClassification::STATUS_CALIFICADO;
        } elseif ($score >= 40) {
            $status = LeadConversationClassification::STATUS_NURTURING;
        }

        return [
            'score' => $score,
            'status' => $status,
        ];
    }
}
