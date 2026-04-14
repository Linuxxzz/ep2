<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta de Usuarios</title>
</head>
<body>

    @extends('layouts.app')
    @section('content')
    <div class="container py-5">
        <main class="admin-content shadow-sm p-4 bg-white rounded">
            @include('partials.alerts')
            
            <header class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <h1 class="fw-bold">CONSULTA DE USUARIOS</h1>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin-dashboard') }}" class="btn btn-outline-primary">
                        <i class="fa-solid fa-gauge"></i> Dashboard
                    </a>
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-house"></i> Tienda
                    </a>
                    <a href="{{ route('registro') }}" class="btn btn-success">
                        <i class="fa-solid fa-user-plus"></i> Nuevo Usuario
                    </a>
                </div>
            </header>

            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Dirección</th>
                            <th>Rol</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($usuarios as $usuario)
                            <tr>
                                <td>{{ $usuario->id }}</td>
                                <td class="fw-bold">{{ $usuario->name }}</td>
                                <td>{{ $usuario->email }}</td>
                                <td>{{ $usuario->direccion ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge {{ $usuario->role == 'admin' ? 'bg-primary' : 'bg-secondary' }}">
                                        {{ ucfirst($usuario->role ?? 'User') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex gap-1 justify-content-center">
                                        @if($usuario->role != 'admin')
                                        <form action="{{ route('admin.usuarios.promote', $usuario->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-warning btn-sm" onclick="return confirm('¿Estás seguro de volver a este usuario administrador?')" title="Hacer Administrador">
                                                <i class="fa-solid fa-user-shield"></i>
                                            </button>
                                        </form>
                                        @endif

                                        <form action="{{ route('admin.usuarios.destroy', $usuario->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este usuario?')" title="Eliminar">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    @endsection
</body>
</html>
