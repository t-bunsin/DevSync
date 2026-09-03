{{--
    Employer-authored prose, rendered the same way the job page's Company tab
    renders it: blank lines start a new paragraph, and a block whose lines all
    begin with "- " becomes a bulleted list instead.
--}}
@foreach (preg_split('/\R{2,}/', trim($body)) as $paragraph)
    @php
        $lines = array_filter(array_map('trim', preg_split('/\R/', trim($paragraph))), fn ($line) => $line !== '');
        $isList = count($lines) > 0 && collect($lines)->every(fn ($line) => str_starts_with($line, '- '));
    @endphp

    @if ($isList)
        <ul class="jf-cprofile-item__list">
            @foreach ($lines as $line)
                <li>{{ substr($line, 2) }}</li>
            @endforeach
        </ul>
    @else
        <p>{!! nl2br(e($paragraph)) !!}</p>
    @endif
@endforeach
