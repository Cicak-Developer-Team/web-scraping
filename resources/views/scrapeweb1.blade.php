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
                    <span>v.1.0.0</span>
                </div>
                <div class="card-body">
                    <div class="mb-3">Website yang cocok untuk alat ini adalah sebagai berikut:<br>
                        <code>{{ env('APP_URL') }}/?page={loop}</code>
                    </div>
                    <form id="scrape-form">
                        <div class="mb-3">
                            <label class="form-label">Masukkan URL</label>
                            <input type="text" id="form-url" class="form-control" name="url">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Masukkan Container Class</label>
                            <input type="text" id="container-class" class="form-control" name="container_class">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Jumlah Halaman (Loop)</label>
                            <input type="number" id="form-loop" class="form-control" name="loop" min="1"
                                max="10" value="10">
                        </div>
                        <div class="mb-3">
                            <button type="button" onclick="getContent()" class="btn btn-primary">
                                SUBMIT
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Table for displaying results -->
        <div id="results-table" class="my-4">
            <h2>Hasil Scraping</h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Page</th>
                        <th>Title</th>
                        <th>Link</th>
                        <th>Image</th>
                    </tr>
                </thead>
                <tbody id="results-body">
                    <!-- Rows will be added here dynamically -->
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

    <script>
        async function getContent() {
            const url = document.getElementById("form-url").value;
            const containerClass = document.getElementById("container-class").value;
            const loop = document.getElementById("form-loop").value;

            try {
                const response = await axios.post('{{ route('scrape') }}', {
                    url: url,
                    container_class: containerClass,
                    loop: loop
                });

                const data = response.data;
                const resultsBody = document.getElementById("results-body");
                resultsBody.innerHTML = ''; // Clear previous results

                // Loop through each page's data
                data.forEach((pageData, pagenumber) => {
                    const page = pageData.page;

                    pageData.data.forEach((item, loop) => {
                        // Create a new row for each item
                        const row = document.createElement("tr");

                        // Page number
                        const pageCell = document.createElement("td");
                        pageCell.textContent = page;
                        row.appendChild(pageCell);

                        // Title
                        const titleCell = document.createElement("td");
                        titleCell.textContent = item.title || 'No Title';
                        row.appendChild(titleCell);

                        // Link
                        const linkCell = document.createElement("td");
                        const link = document.createElement("a");
                        link.href = item.link || '#';
                        link.target = "_blank";
                        link.textContent = item.link;
                        linkCell.appendChild(link);
                        row.appendChild(linkCell);

                        // Image
                        const imageCell = document.createElement("td");
                        const image = document.createElement("img");
                        image.src = item.image || 'default.jpg';
                        image.alt = item.title || 'Image';
                        image.style.width = "100px";
                        imageCell.appendChild(image);
                        row.appendChild(imageCell);

                        // Append the row to the table body
                        resultsBody.appendChild(row);
                    });
                });
            } catch (error) {
                console.error("Error fetching data:", error);
            }
        }
    </script>
</body>

</html>
