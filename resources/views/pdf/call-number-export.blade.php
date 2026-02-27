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
      padding-top: 8px;
      padding-bottom: 6px;
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
      font-size: 17px;
      line-height: 1.2;
      padding-left: 2px;
      padding-right: 2px;
    }
    .label .library-name {
      font-weight: 700;
      letter-spacing: 0.04em;
      font-size: 17px;
      margin-bottom: 2px;
    }
  </style>
</head>
<body>
  @php
    $columns = 7;
    $items = collect($books ?? [])->map(function ($book) {
        if (empty($book)) {
            return null;
        }
        if (is_array($book)) {
            return [
                'call_number' => $book['call_number'] ?? '',
                'category'    => $book['category']['legend'] ?? '',
            ];
        }
        if (is_object($book)) {
            return [
                'call_number' => $book->call_number ?? '',
                'category'    => $book->category->legend ?? '',
            ];
        }
        return null;
    })->filter(function ($item) {
        return !empty($item['call_number']);
    });
  @endphp
  <table>
    <tbody>
      @forelse ($items->chunk($columns) as $chunk)
        <tr>
          @foreach ($chunk as $item)
            @php
              $safeRaw  = (string)($item['call_number'] ?? '');
              $category = (string)($item['category'] ?? '');
              $segments = $safeRaw === '' ? [] : preg_split('/\s+/u', trim($safeRaw));
            @endphp
            <td>
              <div class="label">
                <p class="library-name">{{ $category }}</p>
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
