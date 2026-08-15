@extends('layouts.app')

@section('title', 'LOFBI — KSOP Kelas I Banten | Sistem Informasi Inventarisasi')

@section('content')
    @include('pages.dashboard')
    @include('pages.aset')
    @include('pages.persediaan')
    @include('pages.opname')
    @include('pages.monitoring')
    @include('pages.laporan')
    @include('pages.audit')
    @include('pages.approval')
    @include('pages.master')
    @include('pages.users')
    @include('pages.settings')
@endsection
