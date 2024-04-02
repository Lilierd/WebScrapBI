<li>
  <a {{-- href="{{ $href }}" --}}
     hx-get="{{ $href }}"
     hx-target="main > section:nth-child(2)"
     hx-select="main > section:nth-child(2)"
     hx-swap="outerHTML"
     hx-push-url="true"
     >
    {{ $displayName }}
  </a>
</li>
