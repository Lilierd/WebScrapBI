<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible"
          content="ie=edge">
    <title>Document</title>
  </head>

  <body>

    <h1> {{ $title }} </h1>

    {!! $childComponent !!}

    <iframe height="480px"
            width="720px"
            src="{{ env('APP_URL') . ':7900?password=' . env('SAIL_VNC_PASSWORD') . '&autoconnect=1&resize=scale' }}"></iframe>

  </body>

</html>
