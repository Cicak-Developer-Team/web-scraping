<table>
    <thead>
        <tr>
            <th>NO</th>
            <th>JUDUL</th>
            <th>GAMBAR</th>
            <th>ARTIKEL</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $key => $item)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $item['title'] }}</td>
                <td>{{ $item['gambar'] }}</td>
                <td>{{ $item['content'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
