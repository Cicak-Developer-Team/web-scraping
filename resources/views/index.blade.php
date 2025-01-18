<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scrapper Tools - BaharDev :3</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>

<body>
    <div class="container vh-100 d-flex flex-column justify-content-center align-items-center">
        <h1 class="text-center mb-4">Welcome to Scrapper Tools</h1>
        <div class="list-group list-group-flush" style="min-width: 20vw">
            <a href="{{ route('kompas') }}" class="list-group-item list-group-item-action border">Kompas</a>
            <a href="{{ route('republika') }}" class="list-group-item list-group-item-action border">Republika</a>
            <a href="{{ route('okezone') }}" class="list-group-item list-group-item-action border">Okezone</a>
            <a href="{{ route('kontan') }}" class="list-group-item list-group-item-action border">Kontan</a>
            <a href="{{ route('bisnis') }}" class="list-group-item list-group-item-action border">Bisnis</a>
            <a href="{{ route('pikiranrakyat') }}" class="list-group-item list-group-item-action border">Pikiran
                Rakyat</a>
            <a href="{{ route('mediaindo') }}" class="list-group-item list-group-item-action border">Media Indo</a>
            <a href="{{ route('jawa') }}" class="list-group-item list-group-item-action border">Jawapos</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>
</body>

</html>
