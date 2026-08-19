@props([
    'route' => route('api.keys.index'),
    'name' => 'default',
    'presenter' => \App\Presenters\KeyPresenter::dataTableLayout(),
    'fixed_right_number' => 2,
    'fixed_number' => 2,
    'table_header' => trans('general.keys'),
])

@aware(['name'])

<!-- start keys tab pane -->
@can('view', \App\Models\Key::class)

    <x-slot:table_header>
        {{ $table_header }}
    </x-slot:table_header>

    <x-table
        :$presenter
        :$fixed_right_number
        :$fixed_number
        show_column_search="true"
        show_advanced_search="true"
        buttons="keysButtons"
        api_url="{{ $route }}"
        export_filename="export-{{ str_slug($name) }}-keys-{{ date('Y-m-d') }}"
    />

@endcan
<!-- end keys tab pane -->
