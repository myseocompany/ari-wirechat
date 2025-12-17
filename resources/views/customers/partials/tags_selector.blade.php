@php
    $selectedTags = $selectedTags ?? collect();
    $formId = $formId ?? 'customer-tags-form';
    $formAction = $formAction ?? '';
    $feedbackSelector = $feedbackSelector ?? null;
@endphp

@once
<style>
    .tag-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 4px 0;
        border-radius: 6px;
        border: none;
        background: transparent;
        cursor: pointer;
    }
    .tag-swatch {
        min-width: 32px;
        height: 18px;
        border-radius: 4px;
        clip-path: polygon(0 0, 82% 0, 100% 50%, 82% 100%, 0 100%);
        border: 1px solid #e2e8f0;
        font-size: 10px;
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 4px;
        text-transform: uppercase;
    }
    .tag-checkbox {
        position: absolute;
        opacity: 0;
        pointer-events: none;
        width: 0;
        height: 0;
    }
</style>
@endonce

<form
    method="POST"
    action="{{ $formAction }}"
    id="{{ $formId }}"
    class="customer-tags-form"
    @if($feedbackSelector)
        data-tags-feedback="{{ $feedbackSelector }}"
    @endif
>
    @csrf
    <div class="flex flex-wrap gap-2">
        @foreach($allTags as $tagOption)
            @php
                $checked = $selectedTags->contains($tagOption->id);
                $color = $tagOption->color ?: '#edf2f7';
            @endphp
            <label class="tag-label text-sm">
                <input
                    type="checkbox"
                    name="tags[]"
                    value="{{ $tagOption->id }}"
                    class="form-checkbox tag-checkbox mr-2"
                    data-name="{{ $tagOption->name }}"
                    data-color="{{ $tagOption->color ?: '#e2e8f0' }}"
                    @checked($checked)>
                <span class="tag-swatch"
                      style="border-color: {{ $checked ? $color : '#e2e8f0' }};
                             background-color: {{ $checked ? $color : 'transparent' }};
                             color: {{ $checked ? '#fff' : '#000' }};">
                    {{ strtoupper($tagOption->name) }}
                </span>
            </label>
        @endforeach
    </div>
</form>
