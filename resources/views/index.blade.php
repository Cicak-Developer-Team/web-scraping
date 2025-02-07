<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scrapper Tools - BaharDev :3</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            font-family: 'Arial', sans-serif;
        }

        h1 {
            font-size: 2rem;
            font-weight: bold;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.3);
        }

        .card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 15px;
            border: none;
        }

        .card a {
            display: block;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            padding: 10px;
            margin: 5px 0;
            border-radius: 8px;
            transition: all 0.3s ease-in-out;
            text-align: center;
        }

        .card a:hover {
            background: rgba(255, 255, 255, 0.4);
            transform: scale(1.05);
        }

        *::-webkit-scrollbar {
            width: 0px;
        }
    </style>
</head>

<body>
    <div class="container">

        <h1 class="text-center mb-4">Welcome to Scrapper Tools</h1>

        <div class="row justify-content-center p-0 mm-0">
            <div class="col-md">
                <div class="card p-3 overflow-auto">
                    <a href="{{ route('googlesearch') }}">Google Search</a>
                    <a href="{{ route('kompas') }}">Kompas</a>
                    <a href="{{ route('republika') }}">Republika</a>
                    <a href="{{ route('okezone') }}">Okezone</a>
                    <a href="{{ route('kontan') }}">Kontan</a>
                    <a href="{{ route('bisnis') }}">Bisnis</a>
                    <a href="{{ route('mediaindo') }}">Media Indo</a>
                    <a href="{{ route('detiksport') }}">Detik Sport</a>
                    <a href="{{ route('sindonews') }}">Sindonews</a>
                    <a href="{{ route('boalsport') }}">Boalsport</a>
                    <a href="{{ route('moneykompas') }}">Money.Kompas</a>
                    <a href="{{ route('rmid') }}">RM.id</a>
                    <a href="{{ route('thejakartapost') }}">The Jakarta Post</a>
                    <a href="{{ route('surabayatribunnews') }}">Surabaya Tribunnews</a>
                    <a href="{{ route('sportsindonews') }}">Sport Sindonews</a>
                    <a href="{{ route('investor') }}">Investor</a>
                    <a href="{{ route('skorid') }}">Skor.id</a>
                    <a href="{{ route('gayotribunnews') }}">Gayo Tribunnews</a>
                </div>
            </div>
            <div class="col-md">

                <div class="card p-3 overflow-auto">
                    <a href="{{ route('abmm') }}">ABM Investama Tbk</a>
                    {{-- <a href="{{ route('apex') }}">Apexindo Pratama Duta Tbk</a> --}}
                    <a href="{{ route('bipi') }}">Astrindo Nusantara Infrastrukt</a>
                    {{-- <a href="{{ route('dewa') }}">Darma Henwa Tbk</a> --}}
                    <a href="{{ route('doid') }}">Delta Dunia Makmur Tbk</a>
                    <a href="{{ route('dssa') }}">Dian Swastatika Sentosa Tbk</a>
                    <a href="{{ route('elsa') }}">Elnusa Tbk</a>
                    <a href="{{ route('itmg') }}">Indo Tambangraya Megah Tbk</a>
                    <a href="{{ route('myoh') }}">Samindo Resources Tbk</a>
                    <a href="{{ route('pgas') }}">Perusahaan Gas Negara Tbk</a>
                    <a href="{{ route('ptba') }}">Bukit Asam Tbk</a>
                    <a href="{{ route('raja') }}">Rukun Raharja Tbk</a>
                    <a href="{{ route('smmt') }}">Golden Eagle Energy Tbk</a>
                    {{-- <a href="{{ route('smru') }}">SMR Utama Tbk</a> --}}
                    <a href="{{ route('toba') }}">TBS Energi Utama Tbk</a>
                    <a href="{{ route('pssi') }}">IMC Pelita Logistik Tbk</a>
                    {{-- <a href="{{ route('dwgl') }}">Dwi Guna Laksana Tbk</a> --}}
                    <a href="{{ route('tcpi') }}">Transcoal Pacific Tbk</a>
                    <a href="{{ route('sure') }}">Super Energy Tbk</a>
                    <a href="{{ route('tebe') }}">Dana Brata Luhur Tbk</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
