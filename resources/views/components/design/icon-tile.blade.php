@props(['tone' => 'cloud'])

@php
    $classes = match ($tone) {
        'blush' => 'bg-[color:var(--ds-blush)] text-[color:var(--ds-coral)]',
        'navy' => 'bg-[color:var(--ds-navy)] text-white',
        default => 'bg-[color:var(--ds-cloud)] text-[color:var(--ds-navy)]',
    };
@endphp

<div {{ $attributes->merge(['class' => "flex h-14 w-14 items-center justify-center rounded-2xl shadow-sm {$classes}"]) }}>
    {{ $slot }}
</div>
