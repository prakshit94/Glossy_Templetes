@props(['pageTitle' => ''])

@extends('layouts.app', ['pageTitle' => $pageTitle])

@section('content')
    {{ $slot }}
@endsection
