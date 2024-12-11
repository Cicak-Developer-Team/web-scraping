<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Scrape Dynamic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header text-center">
                        <h2>Scraping Dynamic URL</h2>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">Website yang cocok untuk alat ini adalah sebagai berikut:<br>
                            <code>{{ env('APP_URL') }}/ajax/latest_news/{list_item}/{page}/false/load_more/</code>
                        </div>
                        <form id="scrape-form">
                            {{-- <div class="mb-3">
                                <label class="form-label">Base Url</label>
                                <input type="text" name="list_item" id="list-item" class="form-control" required>
                            </div> --}}
                            <div class="mb-3">
                                <label class="form-label">List Item</label>
                                <input type="text" name="list_item" id="list-item" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Jumlah Halaman (Loop)</label>
                                <input type="number" name="loop" id="loop" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Container Class</label>
                                <input type="text" name="container_class" id="container-class" class="form-control"
                                    required>
                            </div>
                            <button type="submit" class="btn btn-primary">Scrape</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row justify-content-center mt-5">
            <div class="col-md-8">
                <h3 class="text-center">Hasil Scraping</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Page</th>
                            <th>Title</th>
                            <th>Link</th>
                            <th>Image</th>
                        </tr>
                    </thead>
                    <tbody id="results-table">
                        <!-- Data hasil scraping akan ditambahkan di sini -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.getElementById("scrape-form").addEventListener("submit", async function(e) {
            e.preventDefault();

            const listItem = document.getElementById("list-item").value;
            const loop = document.getElementById("loop").value;
            const containerClass = document.getElementById("container-class").value;

            try {
                const response = await axios.post('{{ route('scrapeDynamic') }}', {
                    list_item: listItem,
                    loop: loop,
                    container_class: containerClass
                });

                const data = response.data;
                const resultsTable = document.getElementById("results-table");
                resultsTable.innerHTML = "";

                data.forEach(result => {
                    if (result.data && result.data.length > 0) {
                        result.data.forEach(item => {
                            const row = `
                                <tr>
                                    <td>${result.page}</td>
                                    <td>${item.title}</td>
                                    <td><a href="${item.link}" target="_blank">${item.link}</a></td>
                                    <td><img src="${item.image || '#'}" alt="image" width="100"></td>
                                </tr>`;
                            resultsTable.innerHTML += row;
                        });
                    } else if (result.error) {
                        resultsTable.innerHTML += `<tr><td colspan="4">${result.error}</td></tr>`;
                    }
                });
            } catch (error) {
                console.error("Error fetching data:", error);
                alert("Terjadi kesalahan saat melakukan scraping.");
            }
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
</body>

</html>
