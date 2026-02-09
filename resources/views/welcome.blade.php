@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">

    <div class="col-md-6 col-lg-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Usuarios</h5>
                <p class="card-text">Total registrados: 120</p>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-4">
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Ventas</h5>
                <p class="card-text">$ 15,300</p>
            </div>
        </div>
    </div>

</div>
@endsection
