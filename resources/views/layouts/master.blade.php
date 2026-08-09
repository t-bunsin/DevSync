<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="KH-WORKS connects job seekers with trusted employers across Cambodia and remote-first teams.">
    <meta name="theme-color" id="theme-color" content="#ffffff">
    <script>
        (() => {
            try {
                const savedTheme = localStorage.getItem('khworks:theme');
                const theme = savedTheme === 'dark' || savedTheme === 'light' ? savedTheme : 'light';
                document.documentElement.dataset.theme = theme;
                document.documentElement.style.colorScheme = theme;
                document.getElementById('theme-color')?.setAttribute('content', theme === 'dark' ? '#071015' : '#ffffff');
            } catch (error) {
                document.documentElement.dataset.theme = 'light';
                document.documentElement.style.colorScheme = 'light';
                document.getElementById('theme-color')?.setAttribute('content', '#ffffff');
            }
        })();
    </script>
    <title>@yield('title', 'KH-WORKS')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/fstyle.css') }}">
    @stack('styles')
</head>
<body class="jf-body">
    @include('partials.header')

    <main class="jf-main">
        @yield('content')
    </main>

    @include('partials.footer')

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/site.js') }}"></script>
    @stack('scripts')
</body>
</html>
