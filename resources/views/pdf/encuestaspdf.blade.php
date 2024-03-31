<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Informe de Encuesta</title>
    <link href="https://fonts.googleapis.com/css?family=Arial" rel="stylesheet">

    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            /* Centrado horizontal */
            align-items: center;
            /* Centrado vertical */
            height: 100vh;
            /* Establece la altura del body al 100% del viewport */
        }

        .page-break {
            page-break-before: always;
        }

        .data-table {
            margin: 20px auto;
            border-collapse: collapse;
            width: 90%;
        }


        .data-table,
        .data-table th,
        .data-table td {
            border: 1px solid black;

        }

        .data-table th {
            padding: 8px;
            text-align: left;
            background-color: dimgray;
            color: white;
        }

        .data-table td {
            padding: 8px;
            text-align: left;
        }

        .color-box {
            height: 15px;
            width: 15px;
            display: inline-block;
            margin-right: 5px;
        }

        .blue {
            background-color: #007BFF;
        }

        .red {
            background-color: #FF5733;
        }

        .green {
            background-color: #28A745;
        }

        .purple {
            background-color: #D633FF;
        }

        .header-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .section-title {
            margin-top: 40px;
            margin-bottom: 20px;
            font-size: 24px;
            text-decoration: underline;
        }

        .table {
            width: 98%;
            /* Reducir el ancho de la tabla */
            border-collapse: collapse;
            table-layout: fixed;
            /* Establecer un ancho fijo para las celdas */
            margin-left: 5%;
            /* Agregar margen a la izquierda */
        }

        .table th,
        .table td {
            padding: 8px;
            text-align: left;
            /* Alinear el texto hacia la izquierda */
            word-wrap: break-word;
            /* border: solid 1px; */
        }

        /* Estilos para la recta y segmentos */
        .line-container {
            text-align: center;
            margin-top: 20px;
            position: relative;
        }

        .line {
            width: 85%;
            /* Restar el ancho total menos el grosor del segmento */
            height: 2px;
            background-color: black;
            margin: 0 auto;
            position: absolute;
            left: 0;
            /* Colocar la línea en la posición inicial */
            right: 100px;
            /* Colocar la línea en la posición final */
        }

        .segment {
            position: absolute;
            top: -6.5px;
            width: 1px;
            height: 13px;
            background-color: black;
        }

        /* Posicionar cada segmento */
        .seg-1 {
            left: 0.1%;
            /* Comienza desde la segunda columna */
        }

        .seg-2 {
            left: 20.1%;
        }

        .seg-3 {
            left: 40.2%;
        }

        .seg-4 {
            left: 60.3%;
        }

        .seg-5 {
            left: 80.4%;
        }

        .response {
            /* Estilos actualizados para las respuestas */
            position: absolute;
            top: -40px;
            /* Ajustar según sea necesario para evitar superposición con la recta */
            left: 50%;
            transform: translateX(-50%);
            /* Centrar el texto en el segmento */
            color: black;
            padding: 5px 10px;
            /* Añadir un poco de espacio alrededor del texto */
            border-radius: 10px;
            /* Bordes redondeados para un aspecto más suave */
            white-space: nowrap;
            /* Asegurar que el texto no se envuelva */
            font-size: 12px;
            /* Ajustar el tamaño del texto según sea necesario */
        }

        /* Estilo para la columna N */
        .table th:first-child,
        .table td:first-child {
            text-align: center;
            /* Alinear el texto hacia el centro */
        }

        .parallel-line-container {
            text-align: center;
            position: relative;
            height: 20px;
            /* Altura del contenedor para asegurar un adecuado espaciado */
        }



        .parallel-line {
            width: calc(100% - 4px);
            height: 13px;
            /* Aumentar el grosor de la barra */
            background-color: #3498db;
            /* Ejemplo de color: azul */
            margin: 0 auto;
            position: absolute;
            left: 0;
            right: 0;
            top: 6px;
        }

        .blue-line {
            background-color: #007BFF;
            /* Un azul más vibrante */
        }

        .red-line {
            background-color: #FF5733;
            /* Un rojo más intenso */
        }

        .green-line {
            background-color: #28A745;
            /* Un verde más saturado */
        }

        .yellow-line {
            background-color: yellow;
            /* Ajusta el color amarillo según tus preferencias */
        }

        .orange-line {
            background-color: orange;
            /* Ajusta el color amarillo según tus preferencias */
        }

        .purple-line {
            background-color: #D633FF;
            /* Un púrpura más luminoso */
        }


        .number-on-container {
            position: absolute;
            top: -14px;
            /* Ajuste para posicionar correctamente los números encima de las barras */
            color: black;
            /* O el color que mejor se ajuste al diseño */
            font-size: 10px;
            z-index: 10;
        }

        .container {
            margin-top: 60px;
            /* Ajusta este valor según la altura real de tu .header */
        }


        .bar-container {
            position: relative;
            /* Asegura que los números se posicionen relativo a este contenedor */
            width: 79%;
            /* O un ancho específico si lo prefieres */
            height: 20px;
            /* Altura ajustada del contenedor para visualización adecuada */
            margin-bottom: 20px;
            /* Espacio entre las barras */
            top: 13px;
            left: 8.5px;
            /* border: solid 1px black; */
        }



        /* Ajustes específicos para las barras */
        .blue-line,
        .red-line,
        .green-line,
        .purple-line,
        .orange-line,
        .yellow-line {
            height: 13px;
            /* Aumentar el grosor de las barras */
        }


        .footer {
            position: fixed;
            bottom: -30px;
            margin-left: 50px;
            /* Ajusta la posición a tu preferencia */
            left: 0px;
            right: 0px;
            height: 50px;
            /* Altura del footer */
            /** Añade estilos adicionales aquí **/
            text-align: left;
            line-height: 35px;
            /* Alineación vertical del texto en el footer */
        }

        .footerPrincipal {
            bottom: -30px;
            /* Ajusta la posición a tu preferencia */
            left: 0px;
            right: 0px;
            height: 50px;
            /* Altura del footer */
            /** Añade estilos adicionales aquí **/
            text-align: center;
            line-height: 35px;
            /* Alineación vertical del texto en el footer */
        }

        .header {
            position: fixed;

            /* Ajusta la posición a tu preferencia */
            left: 0px;
            right: 0px;
            height: 8%;
            /* Altura del footer */

            /** Añade estilos adicionales aquí **/
            text-align: left;
            line-height: 35px;
            /* Alineación vertical del texto en el footer */
        }

        .headerdrecho {
            position: fixed;

            /* Ajusta la posición a tu preferencia */
            margin-right: 60px;
            right: 0px;
            height: 8%;
            top: -20px;
            font-size: 100%;
            /** Añade estilos adicionales aquí **/
            text-align: right;
            line-height: 35px;
            /* Alineación vertical del texto en el footer */
        }

        .page-number:before {
            content: counter(page);
            /* Opcional: Añade el número de página */
        }

        .header-section {
            text-align: center;
            font-size: 16px;
            /* Tamaño de fuente más grande */
        }

        .header-section h1,
        .header-section p {
            margin: 0;
            padding: 0;
        }

        .table-container {
            text-align: center;
        }

        .top-table {
            width: 85%;
            margin: 20px auto;
            margin-top: -15px;
        }

        .top-table th,
        .top-table td {
            text-align: left;
            padding: 8px;
            vertical-align: top;
            /* Alinea el contenido hacia arriba */

        }
    </style>
</head>

<body>
    <br><br><br><br><br><br><br>

    <img src="images/logogarza.png" alt="logo" class="img-fluid" style="max-width: 50%; max-height: 50%; margin-left: 25%; margin-top: -15%;" />

    <br><br>
    <div class="header-section">
        <h1>Informe de Feedback 360</h1>
        <br><br><br><br><br><br><br>

        <h2>{{ ucfirst(\Carbon\Carbon::parse($encuesta->fecha)->locale('es')->isoFormat('MMMM [de] YYYY')) }}</h2>
        <?php
        $nombreCompleto = $empresa->representante;
        $partes = explode(' ', $nombreCompleto);

        // Reorganizamos las partes. Asumiendo el formato Apellido Paterno, Apellido Materno, Primer Nombre, Segundo Nombre
        $nombreModificado = $partes[2] . " " . $partes[0]; // Primer Nombre + Apellido Paterno
        $nombreModificado = strtolower($nombreModificado); // Convertimos toda la cadena a minúsculas
        $nombreModificado = ucwords($nombreModificado); // Convertimos a mayúscula la primera letra de cada palabra

        ?>
        <p> {{ $nombreModificado }}</p>
        <br><br>
        <img src="images/confidencial.png" alt="logo" class="img-fluid" style="max-width: 60%; max-height: 60%; ">

    </div>
    <div class="footerPrincipal">
        Diseñado por Garzasoft
    </div>
    <div class="page-break"></div>
    <div class="header">
        <img src="images/logogarza.png" alt="logo" class="img-fluid" style="max-width: 80%; max-height: 80%;  margin-left: 44px;">
        <p class="headerdrecho" style="top: 2px; font-size: 14px"> {{ $nombreModificado }}</p>
    </div>

    <div class="container">
        <div class="row">
            <div class="col">
                <h2 style="font-size: 18px; margin-left: 50px;">Index</h2>
                <table style="width: 85%;" class="data-table">
                    <thead class="thead-dark">
                        <tr>
                            <th>Order</th>
                            <th>Description</th>
                            <th>Section</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>1</td>
                            <td>Resumen General</td>
                            <td>A</td>
                        </tr>
                        <tr>
                            <td>2</td>
                            <td>Resumen por Categoría</td>
                            <td>B</td>
                        </tr>
                        <tr>
                            <td>3</td>
                            <td>Items Individuales</td>
                            <td>C</td>
                        </tr>
                        <tr>
                            <td>4</td>
                            <td>Top 5 Bottom 5</td>
                            <td>D</td>
                        </tr>
                        <tr>
                            <td>5</td>
                            <td>Comentarios Abiertos</td>
                            <td>E</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    <div class="footer">
        Diseñado por Garzasoft
    </div>
    <div class="page-break"></div>

    <div class="container">
        <h2 style="font-size: 18px; margin-left: 50px;">Data</h2>
        <table style="width: 85%;" class="data-table">
            <tr>
                <th>Cargo</th>
                <th>Color</th>
            </tr>
            @foreach ($enviosPorCargo as $index => $cargo)

            <tr>
                <td> {{ $cargo['cargo'] }} </td>
                <td>
                    <div class="@if($index % 6 == 0) blue-line
            @elseif($index % 6 == 1) red-line
            @elseif($index % 6 == 2) green-line
            @elseif($index % 6 == 3) purple-line
            @elseif($index % 6 == 4) orange-line
            @else yellow-line
            @endif"></div>
                </td>
            </tr>
            @endforeach

        </table>
    </div>

    <!-- <span class=" page-number">Página </span> -->

    <div class="page-break"></div>

    <div class="container">
        <h2 style="font-size: 18px; margin-left: 50px;">Resumen General</h2>
        <br>
        <!-- Tabla con los textos de respuestas y una única recta debajo -->
        <table class="table">
            <tr>
                <th></th> <!-- Encabezado de la nueva columna -->
                <th></th> <!-- Encabezado de la nueva columna -->

                @foreach ($respuestas as $index => $respuesta)
                <!-- Cada celda tiene un contenedor que ayuda a alinear los segmentos correctamente -->
                <th>
                    <div style="font-size: 61%; ">{{ $respuesta->texto }}</div>

                </th>
                @endforeach
                <th></th>

            </tr>
            <tr>
                <td></td> <!-- Celda vacía para alinear la línea -->
                <td>
                    <div class="line-container" style="height: 20px;">
                        <div style="position: absolute; top: -30px; font-size: 12px; margin-left: 23px;">N</div>
                    </div>
                </td> <!-- Celda vacía para alinear la línea -->

                <td colspan="{{ count($respuestas) + 1 }}">
                    <div class="line-container" style="height: 20px;">
                        <!-- Cada segmento se posiciona según el porcentaje del ancho total -->
                        <div class="line">
                            @foreach ($respuestas as $index => $respuesta)
                            <!-- Cada segmento se posiciona según el porcentaje del ancho total -->

                            <div class="segment seg-{{ $index + 1 }}" style="left: calc({{ (100 / count($respuestas)) * $index }}%);">
                                <!-- Agregar número al segmento -->

                                <div style="position: absolute; top: -25px; font-size: 12px;">{{ $respuesta->score }}</div>
                            </div>
                            @endforeach

                        </div>
                    </div>
                </td>
                <td>
                    <div class="line-container" style="height: 20px;">
                        <div style="position: absolute; top: -30px; font-size: 12px; right: 75px;">Score</div>
                    </div>
                </td>
            </tr>
            @foreach ($enviosPorCargo as $index => $cargo)
            <tr>
                <td style="font-size: 61%; text-align: right;">{{ $cargo['cargo'] }}</td>
                <td style="font-size: 61%; text-align: center;">{{ $cargo['cantidad_envios'] }}</td>
                <td colspan="{{ count($respuestas) }}" style="position: relative; padding: 0;">
                    <div class="bar-container">
                        @if($cargo['promedio_rango'] > 0)
                        <div class="@if($index % 6 == 0) blue-line
      @elseif($index % 6 == 1) red-line
      @elseif($index % 6 == 2) green-line
      @elseif($index % 6 == 3) purple-line
      @elseif($index % 6 == 4) orange-line
      @else yellow-line
      @endif" style="width: {{ (($cargo['promedio_rango'] - 1) * 25.1) }}%;">
                        </div>

                        @endif

                        <!-- Distribuir las cantidades de rango solo si son mayores que 0 -->
                        @if($cargo['cantidad_rango_1'] > 0)
                        <div class="number-on-container" style="left: 0%;">{{ $cargo['cantidad_rango_1'] }}</div>
                        @endif
                        @if($cargo['cantidad_rango_2'] > 0)
                        <div class="number-on-container" style="left: 25%;">{{ $cargo['cantidad_rango_2'] }}</div>
                        @endif
                        @if($cargo['cantidad_rango_3'] > 0)
                        <div class="number-on-container" style="left: 50%;">{{ $cargo['cantidad_rango_3'] }}</div>
                        @endif
                        @if($cargo['cantidad_rango_4'] > 0)
                        <div class="number-on-container" style="left: 75%;">{{ $cargo['cantidad_rango_4'] }}</div>
                        @endif
                        @if($cargo['cantidad_rango_5'] > 0)
                        <div class="number-on-container" style="right: 0%;">{{ $cargo['cantidad_rango_5'] }}</div>
                        @endif
                    </div>
                </td>
                <td style="font-size: 61%; text-align: right">{{ $cargo['promedio_rango'] }}</td>
            </tr>
            @endforeach


        </table>
    </div>
    <div class="page-break"></div>
    @foreach ($categorias as $index => $categoria)

    <div class="container">
        <h2 class="headerdrecho">Resumen por Categoría</h2>
        <h2 style="font-size: 18px; margin-left: 50px;">{{$categoria->nombre}}</h2>
        <!-- Tabla con los textos de respuestas y una única recta debajo -->
        <table class="table">
            <tr>
                <th></th> <!-- Encabezado de la nueva columna -->
                <th></th> <!-- Encabezado de la nueva columna -->

                @foreach ($respuestas as $index => $respuesta)
                <!-- Cada celda tiene un contenedor que ayuda a alinear los segmentos correctamente -->
                <th>
                    <div style="font-size: 61%; ">{{ $respuesta->texto }}</div>

                </th>
                @endforeach
                <th></th>

            </tr>
            <tr>
                <td></td> <!-- Celda vacía para alinear la línea -->
                <td>
                    <div class="line-container" style="height: 20px;">
                        <div style="position: absolute; top: -30px; font-size: 12px; margin-left: 23px;">N</div>
                    </div>
                </td> <!-- Celda vacía para alinear la línea -->

                <td colspan="{{ count($respuestas) + 1 }}">
                    <div class="line-container" style="height: 20px;">
                        <!-- Cada segmento se posiciona según el porcentaje del ancho total -->
                        <div class="line">
                            @foreach ($respuestas as $index => $respuesta)
                            <!-- Cada segmento se posiciona según el porcentaje del ancho total -->

                            <div class="segment seg-{{ $index + 1 }}" style="left: calc({{ (100 / count($respuestas)) * $index }}%);">
                                <!-- Agregar número al segmento -->

                                <div style="position: absolute; top: -25px; font-size: 12px;">{{ $respuesta->score }}</div>
                            </div>
                            @endforeach

                        </div>
                    </div>
                </td>
                <td>
                    <div class="line-container" style="height: 20px;">
                        <div style="position: absolute; top: -30px; font-size: 12px; right: 75px;">Score</div>
                    </div>
                </td>
            </tr>
            @foreach ($categoria_1Array as $index => $cargo)
            <tr>
                <td style="font-size: 61%; text-align: right;">{{ $cargo['nombre'] }}</td>
                <td style="font-size: 61%; text-align: center;">{{ $cargo['cantidad_envios'] }}</td>
                <td colspan="{{ count($respuestas) }}" style="position: relative; padding: 0;">
                    <div class="bar-container">
                        @if($cargo['promedio_rango'] > 0)
                        <div class="@if($index % 6 == 0) blue-line
                                            @elseif($index % 6 == 1) red-line
                                            @elseif($index % 6 == 2) green-line
                                            @elseif($index % 6 == 3) purple-line
                                            @elseif($index % 6 == 4) orange-line
                                            @else yellow-line
                                            @endif" style="width: {{ (($cargo['promedio_rango'] - 1) * 25.1) }}%;">
                        </div>

                        @endif

                        <!-- Distribuir las cantidades de rango solo si son mayores que 0 -->
                        @if($cargo['cantidad_rango_1'] > 0)
                        <div class="number-on-container" style="left: 0%;">{{ $cargo['cantidad_rango_1'] }}</div>
                        @endif
                        @if($cargo['cantidad_rango_2'] > 0)
                        <div class="number-on-container" style="left: 25%;">{{ $cargo['cantidad_rango_2'] }}</div>
                        @endif
                        @if($cargo['cantidad_rango_3'] > 0)
                        <div class="number-on-container" style="left: 50%;">{{ $cargo['cantidad_rango_3'] }}</div>
                        @endif
                        @if($cargo['cantidad_rango_4'] > 0)
                        <div class="number-on-container" style="left: 75%;">{{ $cargo['cantidad_rango_4'] }}</div>
                        @endif
                        @if($cargo['cantidad_rango_5'] > 0)
                        <div class="number-on-container" style="right: 0%;">{{ $cargo['cantidad_rango_5'] }}</div>
                        @endif
                    </div>
                </td>
                <td style="font-size: 61%; text-align: right">{{ $cargo['promedio_rango'] }}</td>
            </tr>
            @endforeach


        </table>
    </div>
    <div class="page-break"></div>

    @endforeach

    <div class="container">
        <h2 style="font-size: 18px; margin-left: 50px;">Items Individuales</h2>
    </div>
    <div class="page-break"></div>
    @foreach ($enviosPorCargo as $index => $cargo)
    <div class="container">
        <h2 class="headerdrecho">Top 5 Bottom 5</h2>

        <h2 style="font-size: 18px; margin-left: 50px;">{{ $cargo['cargo']}}</h2>
        <h2 style=" background-color:#28A745; font-size: 14px; margin-left: 50px; width: 85%;">Top 5</h2>
        <div class="table-container">

            <table style="width: 85%;" class="top-table">

                <tbody>
                    @foreach ($top5 as $top)
                    @if ($top['NombreCargo'] == $cargo['cargo'])
                    <tr>
                        <td>{{ $top['IdPregunta'] }}</td>
                        <td>{{ $top['TextoPregunta'] }}</td>
                        <td>{{ $top['PromedioScore'] }}</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>

        <h2 style="font-size: 14px; margin-left: 50px; height: 20px; background-color: #FF5733; width: 85%;">Bottom 5</h2>
        <div class="table-container">

            <table style="width: 85%;" class="top-table">

                <tbody>
                    @foreach ($Bottom5 as $Bottom)
                    @if ($Bottom['NombreCargo'] == $cargo['cargo'])
                    <tr>
                        <td>{{ $Bottom['IdPregunta'] }}</td>
                        <td>{{ $Bottom['TextoPregunta'] }}</td>
                        <td>{{ $Bottom['PromedioScore'] }}</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>
    <div class="page-break"></div>
    @endforeach
    <?php
    $preguntasAgrupadas = $preguntasAbiertas->groupBy('preguntaTexto');
    ?>

    @foreach ($preguntasAgrupadas as $preguntaTexto => $preguntas)
    <div class="container">

        <h2 class="headerdrecho">Comentarios Abiertos</h2>

        <h2 style="font-size: 14px; width: 85%; margin-left: 50px; background-color: gainsboro;">
            {{ $preguntaTexto }}
        </h2>
        @foreach ($preguntas as $preg)
        @if ($preg->respuestaTexto)
        <h2 class="@if($preg->cargoNombre == 'Par') blue-line
                                @elseif($preg->cargoNombre == 'Auto Evaluación') red-line
                                @elseif($preg->cargoNombre == 'Reporte Directo') green-line
                                @elseif($preg->cargoNombre == 'Cliente') purple-line
                                @elseif($preg->cargoNombre == 'Your Average') orange-line
                                @else yellow-line
                                @endif" style="font-size: 14px;height: 18px; margin-left: 50px; width: 85%;">
            {{ $preg->cargoNombre }}
        </h2>

        <p style="margin-left: 100px;">{{ $preg->respuestaTexto }}</p>
        @endif
        @endforeach
    </div>
    <div class="page-break"></div>
    @endforeach



</body>

</html>