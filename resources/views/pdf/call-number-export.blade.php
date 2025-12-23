<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Call Number Labels</title>
  <style>
    @page {
      size: letter portrait;
      margin: 12mm;
    }

    body {
      margin: 0;
      padding: 0;
      font-family: "Helvetica", "Arial", sans-serif;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    td {
      border: 1px solid #333;
      box-sizing: border-box;
      text-align: center;
      vertical-align: top;
      padding-top: 15px;
      padding-bottom: 10px;
      height: 100px;
      width: 14.28%;
    }

    .label {
      display: flex;
      flex-direction: column;
      justify-content: flex-start;
      align-items: center;
      gap: 3px;
    }

    .label p {
      margin: 0;
      font-size: 14px;
      line-height: 1.2;
    }

    .label .library-name {
      font-weight: 700;
      letter-spacing: 0.04em;
    }
  </style>
</head>

<body>
  @php
    $columns = 7;
    $items = collect($books ?? [])->map(function ($book) {
        if (empty($book)) {
            return '';
        }

        if (is_array($book)) {
            return $book['call_number'] ?? '';
        }

        if (is_object($book)) {
            return $book->call_number ?? '';
        }

        return is_string($book) ? $book : '';
    })->filter(function ($callNumber) {
        return !empty($callNumber);
    });
  @endphp

  <table>
    <tbody>
      @forelse ($items->chunk($columns) as $chunk)
        <tr>
          @foreach ($chunk as $raw)
            @php
              $safeRaw = (string)($raw ?? '');
              $segments = $safeRaw === '' ? [] : preg_split('/\s+/u', trim($safeRaw));
            @endphp
            <td>
              <div class="label">
                <p class="library-name">BPS Library</p>
                @forelse ($segments as $segment)
                  <p>{{ $segment }}</p>
                @empty
                  <p>&nbsp;</p>
                @endforelse
              </div>
            </td>
          @endforeach

          @for ($i = $chunk->count(); $i < $columns; $i++)
            <td>&nbsp;</td>
          @endfor
        </tr>
      @empty
        <tr>
          <td colspan="{{ $columns }}" style="height: 120px; text-align:center;">No call numbers available.</td>
        </tr>
      @endforelse
    </tbody>
  </table>
</body>

</html>