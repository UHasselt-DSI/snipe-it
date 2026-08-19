@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.keys') }}
@parent
@stop


{{-- Page content --}}
@section('content')
    <x-container>
        <x-box name="key">
            <x-table.keys name="keys" :route="route('api.keys.index')" />
        </x-box>
    </x-container>
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table')
@stop
