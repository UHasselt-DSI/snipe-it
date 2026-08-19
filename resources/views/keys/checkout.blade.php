@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/keys/general.checkout') }}
@parent
@stop

@section('header_right')
    <a href="{{ URL::previous() }}" class="btn btn-primary pull-right">{{ trans('general.back') }}</a>
@stop

{{-- Page content --}}
@section('content')

<x-container columns="2">
    <x-page-column class="col-md-7">

        <x-form route="{{ url()->current() }}" id="checkout_form">

            <x-box header="{{ $key->key_id }}">

            @if ($key->key_id)
                <x-form.static :label="trans('admin/keys/table.key_id')">{{ $key->key_id }}</x-form.static>
            @endif

            @if ($key->location)
                <x-form.static :label="trans('general.location')">{!! $key->location->present()->formattedNameLink !!}</x-form.static>
            @endif

            <x-form.static :label="trans('admin/keys/table.status')">
                {{ $key->isAvailable() ? trans('admin/keys/table.available') : trans('admin/keys/table.checked_out') }}
            </x-form.static>

            <x-input.user-select
                :label="trans('general.user')"
                name="assigned_to"
                :selected="old('assigned_to')"
            />

            <x-form.row
                :label="trans('admin/hardware/form.notes')"
                :item="$key"
                name="note"
                type="textarea"
            />

            <x-slot:customfooter>
                <x-redirect_submit_options
                    index_route="keys.index"
                    :button_label="trans('general.checkout')"
                    :options="[
                        'index' => trans('admin/hardware/form.redirect_to_all', ['type' => trans('general.keys')]),
                        'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.key')]),
                        'target' => trans('admin/hardware/form.redirect_to_checked_out_to'),
                    ]"
                />
            </x-slot:customfooter>

            </x-box>

        </x-form>

    </x-page-column>

    <x-page-column class="col-md-5">
        <livewire:checkout-target-panel type="keys" />
    </x-page-column>

</x-container>

@stop
