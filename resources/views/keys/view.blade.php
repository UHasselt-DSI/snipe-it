@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ $key->key_id }}
    {{ trans('general.key') }}
@parent
@stop

@section('header_right')
    <x-button.info-panel-toggle/>
@endsection

{{-- Page content --}}
@section('content')
    <x-container columns="2">
        <x-page-column class="col-md-9 main-panel">


            <x-tabs>
                <x-slot:tabnav>
                    <x-tabs.history-tab count="{{ $key->history()->count() }}" :model="$key"/>
                </x-slot:tabnav>

                <x-slot:tabpanes>

                    <!-- start history tab pane -->
                    <x-tabs.pane name="history">
                        <x-table.history :model="$key" :route="route('api.keys.history', $key)" :hide_fields="['serial']"/>
                    </x-tabs.pane>
                    <!-- end history tab pane -->

                </x-slot:tabpanes>

            </x-tabs>

        </x-page-column>

        <x-page-column class="col-md-3">
            <x-box class="side-box expanded">
                <x-info-panel :infoPanelObj="$key" :qr_code_url="route('qr_code/common', ['object_type' => 'keys', 'id' => $key->id])">
                    <x-slot:buttons>
                        <x-button.edit :item="$key" :route="route('keys.edit', $key->id)"/>
                        <x-button.checkout :item="$key" route="{{ route('keys.checkout.show', $key->id) }}" />
                        <x-button.checkin :item="$key" route="{{ route('keys.checkin.show', $key->id) }}" />
                        <x-button.delete :item="$key" />
                    </x-slot:buttons>
                </x-info-panel>
            </x-box>

        </x-page-column>
    </x-container>

@endsection

@section('moar_scripts')
@include ('partials.bootstrap-table')
@endsection
