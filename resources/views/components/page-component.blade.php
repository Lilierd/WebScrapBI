<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible"
          content="ie=edge">
    <title>{{ config('app.name') }}</title>
    <script src="https://unpkg.com/htmx.org@1.9.11"
            integrity="sha384-0gxUXCCR8yv9FM2b+U3FDbsKthCI66oH5IA9fHppQq9DDMHuMauqq1ZHBpJxQ0J0"
            crossorigin="anonymous"></script>
    @vite(['resources/css/app.css'])
  </head>

  <body>
    <header>
      <h1>
        <a hx-get={{ route('root') }}
           hx-boost="true"
           hx-push-url="true"
           hx-target="body">
          Accueil
        </a>
      </h1>
      <h3> {{ $title }} </h3>
    </header>
    <main>
      <section>

        <iframe height="480px"
                width="720px"
                src="{{ env('APP_URL') . ':7900?password=' . env('SAIL_VNC_PASSWORD') . '&autoconnect=1&resize=scale' }}"></iframe>
      </section>
      <section>
        {!! $childComponent !!}
      </section>
    </main>

    <footer>
      <p>©️ EICNAM 2024</p>
    </footer>
  </body>

</html>
