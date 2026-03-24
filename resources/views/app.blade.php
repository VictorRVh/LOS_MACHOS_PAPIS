<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- 🔥 TITLE (CLAVE PARA GOOGLE) -->
    <title>
        CETPRO Puno | Intranet CETPRO | Login CETPRO Puno | Sistema Académico
    </title>

    <!-- 🔥 SEO -->
    <meta name="description"
        content="Intranet CETPRO Puno. Accede al sistema mediante login CETPRO para gestionar información académica, usuarios y procesos internos en Puno, Perú.">

    <meta name="keywords" content="
        CETPRO,
        CETPRO Puno,
        Puno,
        CETPRO Puno,
        intranet CETPRO Puno,
        login CETPRO,
        login CETPRO Puno,
        acceso intranet CETPRO,
        sistema CETPRO Puno,
        plataforma CETPRO Puno,
        sistema académico CETPRO,
        educación técnica Puno,
        instituto técnico Perú
    ">

    <meta name="author" content="CETPRO Puno">
    <meta name="robots" content="index, follow">

    <!-- 📍 SEO LOCAL -->
    <meta name="geo.region" content="PE-PUN">
    <meta name="geo.placename" content="Puno, Perú">
    <meta name="geo.position" content="-15.8402;-70.0219">
    <meta name="ICBM" content="-15.8402, -70.0219">

    <!-- 🔗 OPEN GRAPH -->
    <meta property="og:title" content="CETPRO Puno - Intranet y Login">
    <meta property="og:description"
        content="Acceso a la intranet CETPRO Puno. Plataforma académica y administrativa.">
    <meta property="og:type" content="website">
    <meta property="og:image" content="{{ asset('img/iconLogo/CETPRO_Image.ico') }}">
    <meta property="og:url" content="{{ url('/') }}">

    <!-- 🐦 TWITTER -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="CETPRO Puno">
    <meta name="twitter:description" content="Login e intranet CETPRO Puno">
    <meta name="twitter:image" content="{{ asset('img/iconLogo/CETPRO_Image.ico') }}">

    <!-- 🔐 CSRF -->
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- 🔥 FAVICON -->
    <link rel="icon" type="image/x-icon" href="{{ asset('img/iconLogo/CETPRO_Image.ico') }}">

    <!-- 🎨 FONT -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- ⚡ VITE -->
    @vite('resources/js/app.js')
</head>

<body class="box-border p-0 m-0 bg-light-bg text-light-color dark:bg-dark-bg dark:text-dark-color font-inter">

    <noscript>
        <strong>Por favor activar JavaScript para acceder a la intranet CETPRO</strong>
    </noscript>

    <!-- 🔥 CONTENIDO SEO REAL (IMPORTANTE) -->
    <div class="hidden">
        <h1>CETPRO PUNO</h1>

        <h2>Intranet CETPRO PUNO</h2>
        <p>
            Bienvenido a la intranet del CETPRO Puno. Plataforma digital para la gestión académica,
            administrativa y educativa del centro técnico en Puno, Perú.
        </p>

        <h2>Login CETPRO</h2>
        <p>
            Accede al sistema CETPRO mediante el login institucional. Esta plataforma permite a estudiantes,
            docentes y personal administrativo gestionar información académica de forma segura.
        </p>

        <h2>Sistema CETPRO Puno</h2>
        <p>
            El sistema CETPRO Puno está diseñado para optimizar procesos educativos, mejorar la gestión
            institucional y brindar acceso rápido a la información desde cualquier dispositivo.
        </p>
    </div>

    <!-- 🔥 APP -->
    <div id="app"></div>

</body>

</html>