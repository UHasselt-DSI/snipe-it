@props([
    'count' => null,
    'name' => 'keys',
    'icon_type' => 'key',
    'label' => trans('general.keys'),
])
@aware(['class'])

@can('view', \App\Models\Key::class)
    <x-tabs.nav-item
        name="{{ $name }}"
        icon_type="{{ $icon_type }}"
        label="{{ $label }}"
        count="{{ $count }}"
        tooltip="{{ $tooltip ?? $label }}"
    />
@endcan
