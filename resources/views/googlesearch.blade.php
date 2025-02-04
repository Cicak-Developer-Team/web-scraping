<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scraping</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.7.1.slim.min.js"
        integrity="sha256-kmHvs0B+OpCW5GVHUNjv9rOmY0IvSIRcf7zGUDTDQM8=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>

<body>
    <div class="container">
        <div class="d-flex justify-content-center gap-2 my-3">
            <div class="card" style="width: 60vh;">
                <div class="card-header text-center">
                    <h1 class="p-0 m-0 display-6">Web Scraping Tools</h1>
                    <span>v.0.0.1-</span>
                    <code>demo</code>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('googlesearchScrape') }}" id="scrape-form">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Keyword</label>
                            <input type="text" class="form-control" name="keyword">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah Halaman</label>
                            <input type="number" id="form-jumlahHalaman" class="form-control" name="jumlahHalaman"
                                value="10">
                        </div>

                        <div class="mb-3">
                            <button class="btn btn-success">
                                SUBMIT
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>
