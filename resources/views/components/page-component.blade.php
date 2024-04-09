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

  <body hx-get="{{Illuminate\Support\Facades\URL::current()}}"
        hx-boost="true"
        hx-target="main > section:nth-child(2)"
        hx-select="main > section:nth-child(2)"
        hx-swap="outerHTML"
        hx-push-url="true"
        {{-- hx-trigger="every 5s" --}}
        hx-indicator="main > section:nth-child(2)"
        {{-- hx-disabled-elt="this" --}}
        >
    <header>
      <h1>
        <a href={{ route('root') }}>
          Accueil
        </a>
      </h1>
    </header>
    <main>
      <section style="margin: 0px; padding: 0px;">

        <iframe height="100%"
                width="100%"
                src="{{ env('APP_URL') . ':7900?password=' . env('SAIL_VNC_PASSWORD') . '&autoconnect=1&resize=scale' }}"></iframe>
      </section>
      <section>
        <h3> {{ $title }} </h3>
        <div>
            {!! $childComponent !!}
        </div>
      </section>
    </main>

    <footer>
      <p>©️ EICNAM 2024</p>
    </footer>
  </body>

</html>
