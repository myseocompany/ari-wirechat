@props(['initials', 'variant' => 'coral'])

@php
    $classes = match ($variant) {
        'navy' => 'bg-[linear-gradient(135deg,#1c2640,#0f172a)] text-white',
        'mint' => 'bg-[linear-gradient(135deg,#d9f3ee,#e9eef7)] text-[color:var(--ds-navy)]',
        'outline' => 'border border-slate-200 bg-white text-slate-700',
        default => 'bg-[linear-gradient(135deg,#ff8d7a,#ff5c5c)] text-white',
    };
@endphp

<div {{ $attributes->merge(['class' => "flex h-12 w-12 items-center justify-center rounded-full text-sm font-semibold {$classes}"]) }}>
    {{ $initials }}
</div>
