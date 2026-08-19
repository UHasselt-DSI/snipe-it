@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/keys/general.checkin') }}
@parent
@stop

@section('header_right')
    <a href="{{ URL::previous() }}" class="btn btn-primary pull-right">{{ trans('general.back') }}</a>
@stop

{{-- Page content --}}
@section('content')

<x-container class="col-md-7">

    <x-form route="{{ url()->current() }}">

        <x-box header="{{ $key->key_id }}">

            @if ($key->key_id)
                <x-form.static :label="trans('admin/keys/table.key_id')">{{ $key->key_id }}</x-form.static>
            @endif

            @if ($key->location)
                <x-form.static :label="trans('general.location')">{!! $key->location->present()->formattedNameLink !!}</x-form.static>
            @endif

            @if ($key->assignedTo)
                <x-form.static :label="trans('admin/keys/table.checked_out_to')">{!! $key->assignedTo->present()->fullName !!}</x-form.static>
            @endif

            <x-form.row
                :label="trans('admin/hardware/form.notes')"
                :item="$key"
                name="note"
                type="textarea"
            />

            <!-- Checkin date -->
            <div class="form-group {{ $errors->has('checkin_at') ? 'has-error' : '' }}">
                <label for="checkin_at" class="col-md-3 control-label">{{ trans('admin/hardware/form.checkin_date') }}</label>
                <div class="col-md-7">
                    <div class="input-group col-md-5 required" style="padding-left: 0">
                        <x-input.datepicker
                            name="checkin_at"
                            id="checkin_at"
                            :value="old('checkin_at', date('Y-m-d'))"
                            end_date="0d"
                        />
                        <x-form.error name="checkin_at" />
                    </div>
                </div>
            </div>

            <x-slot:customfooter>
                <x-redirect_submit_options
                    index_route="keys.index"
                    :button_label="trans('general.checkin')"
                    :options="[
                        'index' => trans('admin/hardware/form.redirect_to_all', ['type' => trans('general.keys')]),
                        'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.key')]),
                        'target' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.user')]),
                    ]"
                />
            </x-slot:customfooter>

        </x-box>

    </x-form>

</x-container>

@stop
