{{--
    Print layout for dompdf. Deliberately not sharing backoffice.css: dompdf
    renders CSS 2.1, so the preview page's grid, columns, color-mix() and
    custom properties all fall back to nothing. Everything here is tables,
    floats and fixed colours, which dompdf does support.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $resume->full_name }}</title>
    <style>
        @page { margin: 34px 40px; }

        body {
            margin: 0;
            /* DejaVu ships with dompdf and covers accented names. */
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9px;
            line-height: 1.5;
            color: #2b2f36;
        }

        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; }

        .head { border-bottom: 1px solid #d9dde3; padding-bottom: 14px; }
        .monogram {
            width: 54px; height: 54px;
            border: 1px solid #2b2f36;
            text-align: center;
            font-size: 14px;
            letter-spacing: 1px;
        }
        .monogram div { padding-top: 18px; }
        /* dompdf has no object-fit, so the box is sized and the source is
           expected to be roughly square; a portrait crop still reads fine. */
        .photo { width: 54px; height: 54px; border: 1px solid #d9dde3; }
        .name { font-size: 25px; font-weight: normal; letter-spacing: 1px; margin: 0; }
        .contact { color: #6b7280; font-size: 8.5px; margin: 5px 0 0; }

        .row td { border-top: 1px solid #d9dde3; padding: 12px 0; }
        .row td.label { width: 132px; font-size: 10px; color: #2b2f36; }
        .row:first-child td { border-top: 0; }

        .entry { margin-bottom: 11px; }
        .entry:last-child { margin-bottom: 0; }
        .entry .title { font-weight: bold; font-size: 9.5px; }
        .entry .dates { float: right; color: #6b7280; font-size: 8.5px; font-weight: normal; }
        .meta { color: #6b7280; font-size: 8.5px; margin: 1px 0 0; }

        ul { margin: 4px 0 0; padding-left: 13px; }
        li { margin: 0 0 1px; }

        .bar { height: 4px; background: #c9ced6; margin: 4px 0 2px; }

        p { margin: 0; }
    </style>
</head>
<body>
    @php
        $month = fn (?string $value) => \App\Models\Resume::formatMonth($value);
        $contact = array_filter([$resume->email, $resume->phone, $resume->location]);
        $skills = $resume->skillList();
        // Split into the two printed columns, longer half on the left.
        $skillColumns = array_chunk($skills, (int) ceil(count($skills) / 2) ?: 1);
        $languages = array_chunk($resume->section('languages'), 2);
        $photo = $resume->photoDataUri();
    @endphp

    <table class="head">
        <tr>
            <td style="width: 74px;">
                @if ($photo)
                    <img class="photo" src="{{ $photo }}" alt="">
                @else
                    <table class="monogram"><tr><td><div>{{ $resume->initials() }}</div></td></tr></table>
                @endif
            </td>
            <td>
                <h1 class="name">{{ strtoupper($resume->full_name) }}</h1>
                @if ($contact)
                    <p class="contact">{{ implode('  |  ', $contact) }}</p>
                @endif
            </td>
        </tr>
    </table>

    <table>
        @if ($resume->summary)
            <tr class="row">
                <td class="label">{{ __('ui.bo.resumes.section_summary') }}</td>
                <td><p>{{ $resume->summary }}</p></td>
            </tr>
        @endif

        @if ($resume->section('work_history'))
            <tr class="row">
                <td class="label">{{ __('ui.bo.resumes.section_work') }}</td>
                <td>
                    @foreach ($resume->section('work_history') as $job)
                        <div class="entry">
                            <div class="title">
                                <span class="dates">
                                    {{ $month($job['started_on'] ?? null) }}@if (($job['started_on'] ?? null) || ($job['ended_on'] ?? null)) - {{ $month($job['ended_on'] ?? null) ?: 'Present' }}@endif
                                </span>
                                {{ $job['role'] ?? 'Untitled role' }}
                            </div>
                            @php $line = array_filter([$job['company'] ?? null, $job['location'] ?? null]); @endphp
                            @if ($line)
                                <p class="meta">{{ implode('  |  ', $line) }}</p>
                            @endif
                            @if (! empty($job['bullets']))
                                <ul>
                                    @foreach ($job['bullets'] as $bullet)
                                        <li>{{ $bullet }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </td>
            </tr>
        @endif

        @if ($skills)
            <tr class="row">
                <td class="label">{{ __('ui.bo.resumes.section_skills') }}</td>
                <td>
                    <table>
                        <tr>
                            @foreach ($skillColumns as $column)
                                <td style="width: 50%;">
                                    <ul>
                                        @foreach ($column as $skill)
                                            <li>{{ $skill }}</li>
                                        @endforeach
                                    </ul>
                                </td>
                            @endforeach
                        </tr>
                    </table>
                </td>
            </tr>
        @endif

        @if ($resume->section('certifications'))
            <tr class="row">
                <td class="label">{{ __('ui.bo.resumes.section_certifications') }}</td>
                <td>
                    <ul>
                        @foreach ($resume->section('certifications') as $certificate)
                            <li>{{ implode(' - ', array_filter([$certificate['name'] ?? null, $certificate['issuer'] ?? null])) }}</li>
                        @endforeach
                    </ul>
                </td>
            </tr>
        @endif

        @if ($resume->section('education'))
            <tr class="row">
                <td class="label">{{ __('ui.bo.resumes.section_education') }}</td>
                <td>
                    @foreach ($resume->section('education') as $study)
                        <div class="entry">
                            <div class="title">
                                <span class="dates">{{ $month($study['graduated_on'] ?? null) }}</span>
                                {{ $study['degree'] ?? 'Qualification' }}@if (! empty($study['field'])): <span style="font-weight: normal;">{{ $study['field'] }}</span>@endif
                            </div>
                            @php $line = array_filter([$study['institution'] ?? null, $study['location'] ?? null]); @endphp
                            @if ($line)
                                <p class="meta">{{ implode('  |  ', $line) }}</p>
                            @endif
                        </div>
                    @endforeach
                </td>
            </tr>
        @endif

        @if ($resume->section('languages'))
            <tr class="row">
                <td class="label">{{ __('ui.bo.resumes.section_languages') }}</td>
                <td>
                    <table>
                        @foreach ($languages as $pair)
                            <tr>
                                @foreach ($pair as $language)
                                    <td style="width: 50%; padding-right: 18px;">
                                        <strong>{{ $language['name'] ?? '' }}</strong>
                                        <div class="bar"></div>
                                        <span class="meta">{{ $language['level'] ?? '' }}</span>
                                    </td>
                                @endforeach
                                @if (count($pair) === 1)
                                    <td style="width: 50%;"></td>
                                @endif
                            </tr>
                        @endforeach
                    </table>
                </td>
            </tr>
        @endif
    </table>
</body>
</html>
