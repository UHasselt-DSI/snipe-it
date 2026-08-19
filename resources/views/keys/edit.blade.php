@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ ($item->id) ? trans('admin/keys/general.update') : trans('admin/keys/general.create') }}
@parent
@stop

@section('header')
    {{ ($item->id) ? trans('admin/keys/general.update') : trans('admin/keys/general.create') }}
@stop

@section('header_right')
    <a href="{{ route('keys.index') }}" class="btn btn-primary">
        {{ trans('general.back') }}
    </a>
@stop

{{-- Page content --}}
@section('content')

    <x-container class="col-lg-8 col-lg-offset-2 col-md-10 col-md-offset-1 col-sm-12 col-sm-offset-0">

        <x-form :$item route="{{ isset($item->id) ? route('keys.update', $item->id) : route('keys.store') }}">

            <x-box top_submit>
                @if ($item->id)
                    <x-slot:header>{{ $item->key_id }}</x-slot:header>
                @endif

                <!-- Key ID -->
                <x-form.row
                    :label="trans('admin/keys/table.key_id')"
                    :$item
                    name="key_id"
                />

                <!-- Location -->
                <x-input.location-select
                    :label="trans('general.location')"
                    name="location_id"
                    :selected="old('location_id', $item->location_id)"
                />

                <!-- Notes -->
                <x-form.row
                    :label="trans('general.notes')"
                    :$item
                    name="notes"
                    type="textarea"
                />

                <x-slot:customfooter>
                    <x-redirect_submit_options
                        index_route="keys.index"
                        :button_label="trans('general.save')"
                        :options="[
                            'back' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.previous_page')]),
                            'index' => trans('admin/hardware/form.redirect_to_all', ['type' => trans('general.keys')]),
                            'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.key')]),
                        ]"
                    />
                </x-slot:customfooter>

            </x-box>

        </x-form>

    </x-container>

@stop
