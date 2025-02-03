@if (!empty($data) && is_array($data))
    <table>
        <thead>
            <tr>
                @foreach (array_keys($data[0]) as $key)
                    <th>{{ ucfirst($key) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $item)
                <tr>
                    @foreach ($item as $value)
                        <td>{{ $value }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p>Tidak ada data tersedia.</p>
@endif
