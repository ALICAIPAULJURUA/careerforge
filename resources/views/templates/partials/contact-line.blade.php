@props(['personal'])
<div class="text-sm text-gray-600 flex flex-wrap gap-x-4 gap-y-1 justify-center">
    @if($personal['email'])<span>{{ $personal['email'] }}</span>@endif
    @if($personal['phone'])<span>{{ $personal['phone'] }}</span>@endif
    @if($personal['location'])<span>{{ $personal['location'] }}</span>@endif
    @if($personal['website_url'])<a href="{{ $personal['website_url'] }}" class="hover:underline" style="color: var(--primary-color);">{{ $personal['website_url'] }}</a>@endif
    @if($personal['linkedin_url'])<a href="{{ $personal['linkedin_url'] }}" class="hover:underline" style="color: var(--primary-color);">LinkedIn</a>@endif
    @if($personal['github_url'])<a href="{{ $personal['github_url'] }}" class="hover:underline" style="color: var(--primary-color);">GitHub</a>@endif
</div>
