<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <link rel="icon" href="{{ asset('img/BPSLogo.png') }}">
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Anton&family=Bebas+Neue&family=Roboto+Mono:ital,wght@0,100..700;1,100..700&family=Rubik+Mono+One&display=swap');

    .roboto-mono {
      font-family: "Roboto Mono", monospace;
      font-optical-sizing: auto;
      font-weight: 600;
      font-style: normal;
      font-size: 25px;
    }

    p {
      max-height: 35px;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    p::after {
      content: "";
      border: 8px solid #0050B5;
      height: 23px;
      animation: blink 1s infinite;
    }

    @keyframes blink {
      50% {
        opacity: 0;
      }
    }
  </style>
  <title>404 Not Found</title>
</head>

<body class="bg-blue-50 dark:bg-gray-900 text-gray-900 dark:text-white">
  <div class="bg-blue-50 dark:bg-gray-900 text-gray-900 dark:text-white flex flex-col items-start ml-20 roboto-mono justify-center min-h-screen">
    <div class="error">
      <p class="flex justify-center items-center after:border-3 after:border-blue-500 dark:after:bg-yellow-600 h-20"></p>
    </div>
  </div>
  <script>
    const text = "404, page not found.";
    const errorText = document.querySelector(".error p");

    let index = 0;

    function typeWriter() {
      if (index < text.length) {
        // Add the current character to the element
        errorText.textContent += text[index];
        index++;
        setTimeout(typeWriter, 100); // Adjust the delay as needed
      }
    }
    typeWriter();
  </script>
</body>

</html>