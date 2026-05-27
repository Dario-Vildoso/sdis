@props(['disabled' => false,
        'icon' => 'ri ri-user-line',
        'icon_bg' => 'bg-success',])
<div class="input-group">
    <span class="input-group-text text-white {{ $icon_bg }}"><i class="{{ $icon }}"></i></span>
    <input @disabled($disabled) {{ $attributes->merge(['class' => 'form-control']) }}>
</div>
