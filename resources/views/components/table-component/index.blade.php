<table>
  <thead>

    <tr>

      @foreach ($headers as $header)
        <th scope="col">{{ $header }}</th>
        {{-- @dd($header) --}}
      @endforeach

    </tr>
  </thead>
  <tbody>
    @foreach ($rows as $index => $row)
      <tr>
        @foreach ($headers as $header)
          <td>
            {{ $row[$header] }}
          </td>
        @endforeach

      </tr>
  </tbody>
  @endforeach
</table>
