@if($personal->isNotEmpty())
    <table class="table">
        <thead>
            <tr>
                <th>DNI</th>
                <th>Nombre</th>
                <th>Cargo</th>
                <th>Email</th>
                <th>Telefono</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($personal as $persona)
                <tr>
                    <td>{{ $persona->dni }}</td>
                    <td>{{ $persona->nombre }}</td>
                    <td>{{ $persona->Cargo }}</td>
                    <td>{{ $persona->correo }}</td>
                    <td>{{ $persona->telefono }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p>No hay personal registrado para esta empresa.</p>
@endif
