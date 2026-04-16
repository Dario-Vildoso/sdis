@extends('layouts.materio')

@section('title', 'Oportunidad de la información')

@section('content')
<div class="card">
    <div class="card-body">
        <h4 class="card-title mb-3">Oportunidad de la información</h4>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="GET" action="{{ route('reportes.oportunidad') }}" class="mb-4">
            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Formulario</label>
                    <select name="formulario_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Seleccione...</option>
                        @foreach($formularios as $item)
                            <option value="{{ $item->id }}" {{ (int) request('formulario_id') === $item->id ? 'selected' : '' }}>
                                {{ $item->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Periodo DESDE</label>
                    <select name="periodo_desde_id" class="form-select" {{ request('formulario_id') ? '' : 'disabled' }}>
                        <option value="">Seleccione...</option>
                        @foreach($periodos as $periodo)
                            <option value="{{ $periodo->id }}" {{ (int) $periodoDesde === $periodo->id ? 'selected' : '' }}>
                                {{ $periodo->descripcion }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Periodo HASTA</label>
                    <select name="periodo_hasta_id" class="form-select" {{ request('formulario_id') ? '' : 'disabled' }}>
                        <option value="">Seleccione...</option>
                        @foreach($periodos as $periodo)
                            <option value="{{ $periodo->id }}" {{ (int) $periodoHasta === $periodo->id ? 'selected' : '' }}>
                                {{ $periodo->descripcion }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-grid">
                    <button type="submit" class="btn btn-primary" {{ request('formulario_id') ? '' : 'disabled' }}>
                        Ver tabla
                    </button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-bordered table-striped align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Red</th>
                        <th>Municipio</th>
                        <th>Establecimiento</th>
                        <th>Producción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($filas as $fila)
                        <tr>
                            <td>{{ $fila->red_nombre }}</td>
                            <td>{{ $fila->municipio_nombre }}</td>
                            <td>{{ $fila->establecimiento_nombre }}</td>
                            <td>{{ $fila->produccion }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">
                                Selecciona formulario y periodo para visualizar la oportunidad de la información.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
