<?php

namespace App\Services\LeadClassifier;

use App\Models\LeadConversationClassification;
use App\Models\Tag;

class LeadTagSuggester
{
    /**
     * @param  array<string, bool|int|null>  $signals
     */
    public function suggest(array $signals, int $score, string $status): ?int
    {
        $pideCitaFabrica = ($signals['pide_cita_fabrica'] ?? false) === true;
        $pideLlamada = ($signals['pide_llamada'] ?? false) === true;
        $tieneProductos = ($signals['tiene_productos'] ?? false) === true;
        $volumenMayor500 = ($signals['volumen_mayor_500'] ?? false) === true;

        if ($pideCitaFabrica) {
            return $this->getTagIdBySlug('sql');
        }

        if ($pideLlamada && ($tieneProductos || $volumenMayor500 || $score >= 70)) {
            return $this->getTagIdBySlug('sql');
        }

        if ($status === LeadConversationClassification::STATUS_CALIFICADO) {
            return $this->getTagIdBySlug('mql');
        }

        if ($status === LeadConversationClassification::STATUS_NURTURING) {
            return $this->getTagIdBySlug('chat');
        }

        return $this->getTagIdBySlug('descalificado');
    }

    private function getTagIdBySlug(string $slug): ?int
    {
        /** @var Tag|null $tag */
        $tag = Tag::query()
            ->where('slug', $slug)
            ->first(['id']);

        return $tag?->id ? (int) $tag->id : null;
    }
}
