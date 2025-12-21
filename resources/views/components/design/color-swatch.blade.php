@props(['name', 'hex', 'labelClass' => 'text-white/70', 'valueClass' => 'text-white'])

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200 p-3 shadow-sm']) }} style="background-color: {{ $hex }};">
    <p class="ds-mono text-xs uppercase tracking-[0.3em] {{ $labelClass }}">{{ $name }}</p>
    <p class="ds-mono text-xs {{ $valueClass }}">{{ $hex }}</p>
</div>
