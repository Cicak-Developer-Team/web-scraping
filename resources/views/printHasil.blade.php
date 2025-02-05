@if (!empty($data) && is_array($data))
    <table>
        <thead>
            <tr>
                <th>NO</th>
                @foreach (array_keys($data[0]) as $key)
                    <th>{{ ucfirst($key) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $key => $item)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    @foreach ($item as $value)
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p style="color: red;">Tidak ada data tersedia.</p>
@endif
