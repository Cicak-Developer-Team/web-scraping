<table>
    <thead>
        <tr>
            <th>NO</th>
            <th>JUDUL</th>
            <th>LINK</th>
            <th>SUB</th>
            <th>ARTIKEL</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data as $key => $item)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $item['title'] }}</td>
                <td>{{ $item['link'] }}</td>
                <td>{{ $item['sub'] }}</td>
                <td>{{ $item['artikel'] }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
