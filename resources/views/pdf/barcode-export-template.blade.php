<!DOCTYPE html>
<html>

<head>
  <title>Barcode PDF</title>
  <style>
    body {
      font-family: sans-serif;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    td {
      width: 50%;
      text-align: center;
      border: 1px solid black;
      box-sizing: border-box;
    }

    p {
      text-align: center;
      font-weight: bold;
      font-size: 9px;
      margin: 2px;
    }

    .barcode-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .barcode-image {
      max-width: 97%;
      height: auto;
      padding: 0;
      margin: 0;
    }

    .barcode-text {
      font-size: 7px;
      margin-top: 5px;
      padding: 0;
      font-style: normal;
    }
  </style>
</head>

<body>
  <table>
    @for ($i = 0; $i < count($books); $i+=2)
      <tr>
      <td>
        <div class="barcode-container">
          <p>BPS LIBRARY</p>
          <img class="barcode-image" src="data:image/jpg;base64,{{ $books[$i]->barcode }}">
          <p class="barcode-text">{{ $books[$i]->accession }}</p>
        </div>
      </td>
      <td>
        <div class="barcode-container">
          <p>BPS LIBRARY</p>
          <img class="barcode-image" src="data:image/jpg;base64,{{ $books[$i]->barcode }}">
          <p class="barcode-text">{{ $books[$i]->accession }}</p>
        </div>
      </td>
      @if(isset($books[$i + 1]))
      <td>
        <div class="barcode-container">
          <p>BPS LIBRARY</p>
          <img class="barcode-image" src="data:image/jpg;base64,{{ $books[$i + 1]->barcode }}">
          <p class="barcode-text">{{ $books[$i + 1]->accession }}</p>
        </div>
      </td>
      <td>
        <div class="barcode-container">
          <p>BPS LIBRARY</p>
          <img class="barcode-image" src="data:image/jpg;base64,{{ $books[$i + 1]->barcode }}">
          <p class="barcode-text">{{ $books[$i + 1]->accession }}</p>
        </div>
      </td>
      @else
      <td></td>
      <td></td>
      @endif
      </tr>
      @endfor
  </table>
</body>

</html>