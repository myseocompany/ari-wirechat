@props(['tone' => 'neutral'])

@php
    $classes = match ($tone) {
        'coral' => 'bg-[color:var(--ds-coral)] text-white',
        'navy' => 'bg-[color:var(--ds-navy)] text-white',
        'blush' => 'bg-[color:var(--ds-blush)] text-[color:var(--ds-coral)]',
        'cloud' => 'bg-[color:var(--ds-cloud)] text-[color:var(--ds-navy)]',
        'mint' => 'bg-[color:var(--ds-mint)] text-[color:var(--ds-navy)]',
        'outline' => 'border border-slate-300 text-slate-700',
        default => 'border border-slate-200 bg-white text-slate-700',
    };
@endphp

<span {{ $attributes->merge(['class' => "rounded-full px-4 py-2 text-xs font-semibold {$classes}"]) }}>
    {{ $slot }}
</span>
