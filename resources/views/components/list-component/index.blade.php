{{-- @aware(['list' => []]) --}}
{{-- @aware(['title' => '']) --}}

<ul>
  {{-- @dump($title) --}}
  {{-- @dump($list) --}}
  @foreach ($list as $listItem)
    <x-list-component.item :display-name="$listItem['display_name']"
                           :href="$listItem['href']" />
  @endforeach
</ul>
