@props([
    'pageTitle' => '',
    'hideSidebar' => false,
    'hideHeaderSearch' => false,
    'hideDashboardLink' => false
])

@extends('layouts.app', [
    'pageTitle' => $pageTitle,
    'hideSidebar' => $hideSidebar,
    'hideHeaderSearch' => $hideHeaderSearch,
    'hideDashboardLink' => $hideDashboardLink
])

@section('content')
    {{ $slot }}
@endsection
