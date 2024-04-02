<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible"
          content="ie=edge">
    <title>Document</title>

    @vite(['resources/css/app.css'])
  </head>

  <body>
    <header>
      <h1> {{ $title }} </h1>
    </header>
    <main>
      <section>

        <iframe
                height="480px"
                width="720px"
                src="{{ env('APP_URL') . ':7900?password=' . env('SAIL_VNC_PASSWORD') . '&autoconnect=1&resize=scale' }}"></iframe>
      </section>
      <section>
        {{-- <div class="overflow-wrapper"> --}}
            {!! $childComponent !!}
        {{-- </div> --}}
      </section>
    </main>

    <footer>
      <p>©️ EICNAM 2024</p>
    </footer>
  </body>

</html>
