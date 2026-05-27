@extends('layouts.materio')

@section('title', 'Perfil')

@section('content')
    <div class="row justify-content-center">
        <div class="col-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="d-flex align-items-center justify-content-between">
                    </div>
                </div>
                <div class="card-body">
                    <div class="row justify-content-center">
                        <div class="">
                            @include('profile.partials.update-password-form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
