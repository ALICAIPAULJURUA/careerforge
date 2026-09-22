@php
    // Accept both DTO object and array
    $meta = is_object($resume) ? $resume->meta : $resume['meta'];
    $personal = is_object($resume) ? $resume->personal : $resume['personal'];
    $sections = is_object($resume) ? $resume->sections : $resume['sections'];
    $theme = $meta['theme'] ?? null;
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $meta['name'] ?? 'Resume' }} — Preview</title>
    <style>
        :root {
            --primary-color: {{ $theme['primary_color'] ?? '#2563eb' }};
            --secondary-color: {{ $theme['secondary_color'] ?? '#64748b' }};
            --text-color: {{ $theme['text_color'] ?? '#1e293b' }};
            --background-color: {{ $theme['background_color'] ?? '#ffffff' }};
            --sidebar-color: {{ $theme['sidebar_color'] ?? '#f1f5f9' }};
            --font-family: '{{ $theme['font_family'] ?? 'Inter' }}', sans-serif;
            --heading-size: {{ ($theme['heading_size'] ?? 'md') === 'sm' ? '14' : (($theme['heading_size'] ?? 'md') === 'lg' ? '18' : '16') }}px;
            --body-size: {{ ($theme['body_size'] ?? 'md') === 'sm' ? '12' : (($theme['body_size'] ?? 'md') === 'lg' ? '14' : '13') }}px;
        }
        body {
            font-family: var(--font-family);
            color: var(--text-color);
            background-color: var(--background-color);
            font-size: var(--body-size);
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .page {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px;
            background: var(--background-color);
        }
        .header {
            text-align: center;
            border-bottom: 3px solid var(--primary-color);
            padding-bottom: 16px;
            margin-bottom: 24px;
        }
        .header h1 {
            font-size: 28px;
            font-weight: bold;
            color: var(--primary-color);
            margin: 0;
        }
        .header .title {
            font-size: var(--heading-size);
            color: var(--secondary-color);
            margin-top: 4px;
        }
        .section {
            margin-bottom: 24px;
        }
        .item {
            margin-bottom: 16px;
        }
        .item-header {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
        }
        .item-title {
            font-weight: 600;
            color: var(--text-color);
        }
        .item-subtitle {
            color: var(--secondary-color);
            font-size: 0.9em;
        }
        .achievements {
            margin-top: 6px;
            padding-left: 18px;
        }
        .achievements li {
            margin-bottom: 4px;
        }
        .skill-group {
            display: inline-block;
            background: var(--sidebar-color);
            padding: 4px 8px;
            border-radius: 4px;
            margin: 4px 4px 0 0;
            font-size: 0.85em;
        }
        .tech-tag {
            display: inline-block;
            background: #e2e8f0;
            padding: 2px 6px;
            border-radius: 3px;
            margin: 2px;
            font-size: 0.8em;
        }
        @media print {
            .page { padding: 20px; }
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>
<div class="page">
    <!-- Header -->
    <div class="header">
        <h1>{{ $personal['full_name'] ?? 'Your Name' }}</h1>
        @if($personal['professional_title'])
            <div class="title">{{ $personal['professional_title'] }}</div>
        @endif
        @include('templates.partials.contact-line', ['personal' => $personal])
        @if($meta['target_role'])
            <div class="text-sm text-gray-500 mt-2">{{ __('Target Role') }}: {{ $meta['target_role'] }}</div>
        @endif
        @if($personal['photo_url'] && ($meta['photo_enabled'] ?? true))
            <div class="mt-4">
                <img src="{{ $personal['photo_url'] }}" alt="Photo" class="mx-auto rounded-full object-cover" style="width: 100px; height: 100px; border: 3px solid var(--primary-color); @if(($meta['photo_style'] ?? 'circle') === 'square') border-radius: 0; @elseif(($meta['photo_style'] ?? 'circle') === 'rounded') border-radius: 8px; @else border-radius: 50%; @endif">
            </div>
        @endif
    </div>

    <!-- Sections -->
    @forelse($sections as $section)
        <div class="section">
            @include('templates.partials.section-heading', ['title' => $section['title']])

            @if(empty($section['items']))
                <p class="text-sm text-gray-400 italic">{{ __('No items selected for this section.') }}</p>
            @else
                @if($section['type'] === 'experience')
                    @foreach($section['items'] as $exp)
                        <div class="item">
                            <div class="item-header">
                                <div>
                                    <div class="item-title">{{ $exp['job_title'] ?? '' }} <span class="item-subtitle">@ {{ $exp['organization'] ?? '' }}</span></div>
                                    @if(!empty($exp['location']))<div class="text-sm text-gray-500">{{ $exp['location'] }}</div>@endif
                                </div>
                                <div>
                                    @include('templates.partials.date-range', ['start' => $exp['start_date'] ?? null, 'end' => $exp['end_date'] ?? null, 'isCurrent' => $exp['is_current'] ?? false])
                                </div>
                            </div>
                            @if(!empty($exp['description']))
                                <p class="text-sm mt-1">{{ $exp['description'] }}</p>
                            @endif
                            @if(!empty($exp['achievements']))
                                <ul class="achievements list-disc">
                                    @foreach($exp['achievements'] as $ach)
                                        <li>{{ $ach['content'] ?? '' }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach

                @elseif($section['type'] === 'education')
                    @foreach($section['items'] as $edu)
                        <div class="item">
                            <div class="item-header">
                                <div>
                                    <div class="item-title">{{ $edu['qualification'] ?? '' }} @ {{ $edu['institution'] ?? '' }}</div>
                                    @if(!empty($edu['field_of_study']))<div class="text-sm text-gray-500">{{ $edu['field_of_study'] }}</div>@endif
                                </div>
                                <div>
                                    @include('templates.partials.date-range', ['start' => $edu['start_date'] ?? null, 'end' => $edu['end_date'] ?? null, 'isCurrent' => $edu['is_current'] ?? false])
                                </div>
                            </div>
                            @if(!empty($edu['description']))<p class="text-sm mt-1">{{ $edu['description'] }}</p>@endif
                        </div>
                    @endforeach

                @elseif($section['type'] === 'skills')
                    <div class="skills">
                        @foreach($section['items'] as $skill)
                            <span class="skill-group" style="background-color: var(--sidebar-color);">{{ $skill['name'] ?? '' }}@if(!empty($skill['category'])) <span class="text-xs text-gray-500">({{ $skill['category'] }})</span>@endif</span>
                        @endforeach
                    </div>

                @elseif($section['type'] === 'projects')
                    @foreach($section['items'] as $proj)
                        <div class="item">
                            <div class="item-title">{{ $proj['name'] ?? '' }} @if(!empty($proj['role']))<span class="item-subtitle">— {{ $proj['role'] }}</span>@endif</div>
                            @if(!empty($proj['description']))<p class="text-sm mt-1">{{ $proj['description'] }}</p>@endif
                            @if(!empty($proj['technologies']))
                                <div class="mt-1">
                                    @foreach($proj['technologies'] as $tech)
                                        <span class="tech-tag">{{ $tech['name'] ?? $tech }}</span>
                                    @endforeach
                                </div>
                            @endif
                            @if(!empty($proj['project_url']) || !empty($proj['github_url']))
                                <div class="text-xs mt-1">
                                    @if(!empty($proj['project_url']))<a href="{{ $proj['project_url'] }}" style="color: var(--primary-color);">{{ $proj['project_url'] }}</a>@endif
                                    @if(!empty($proj['github_url'])) <span class="mx-1">|</span> <a href="{{ $proj['github_url'] }}" style="color: var(--primary-color);">GitHub</a>@endif
                                </div>
                            @endif
                            @if(!empty($proj['start_date']) || !empty($proj['end_date']))
                                <div class="text-xs text-gray-500 mt-1">
                                    @include('templates.partials.date-range', ['start' => $proj['start_date'] ?? null, 'end' => $proj['end_date'] ?? null])
                                </div>
                            @endif
                        </div>
                    @endforeach

                @else
                    @foreach($section['items'] as $item)
                        <div class="item">
                            <pre class="text-xs bg-gray-50 p-2 rounded">{{ json_encode($item, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    @endforeach
                @endif
            @endif
        </div>
    @empty
        <p class="text-center text-gray-400 py-12">{{ __('No sections configured. Add sections to your resume.') }}</p>
    @endforelse
</div>
</body>
</html>
