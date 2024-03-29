{{-- @aware(['list' => []]) --}}

<ul>
  {{-- @dump($list) --}}
  @foreach ($list as $listItem)
    <x-list-component.item :display-name="$listItem['display_name']"
                           :href="$listItem['href']" />
  @endforeach
</ul>
