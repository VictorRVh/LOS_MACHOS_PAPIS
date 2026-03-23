<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <title>INTRANET CETPRO</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('img/iconLogo/CETPRO_Image.ico') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite('resources/js/app.js')
</head>

<body class="box-border p-0 m-0 bg-light-bg text-light-color dark:bg-dark-bg dark:text-dark-color font-inter overflow-hidden">
    <noscript>
        <strong>Por favor activar js</strong>
    </noscript>

    <div id="app"></div>
</body>

</html>