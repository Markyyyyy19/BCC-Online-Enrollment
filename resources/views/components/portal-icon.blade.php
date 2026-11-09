@props(['name' => 'applications'])
<svg {{ $attributes->class(['portal-icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('plus') <path d="M12 5v14M5 12h14"/> @break
        @case('student') <path d="m3 5 9-3 9 3-9 3-9-3Zm18 0v5M8 7v3a4 4 0 0 0 8 0V7M5 22v-2a7 7 0 0 1 14 0v2"/> @break
        @case('person') <circle cx="12" cy="7" r="4"/><path d="M5 22v-2a7 7 0 0 1 14 0v2"/> @break
        @case('chevron') <path d="m7 10 5 5 5-5"/> @break
        @case('students') <circle cx="9" cy="8" r="3"/><path d="M3 21v-3a6 6 0 0 1 12 0v3M16 5a3 3 0 0 1 0 6M21 21v-3a6 6 0 0 0-3-5"/> @break
        @case('programs') <path d="m2 9 10-5 10 5-10 5L2 9Zm4 2v6l6 3 6-3v-6M22 9v7"/> @break
        @case('subjects') <path d="M12 5v15M3 4h5a4 4 0 0 1 4 2 4 4 0 0 1 4-2h5v15h-5a4 4 0 0 0-4 2 4 4 0 0 0-4-2H3Z"/> @break
        @case('grades') <path d="M5 3h14v18H5ZM8 8h8M8 12h3m2 4 2 2 4-4"/> @break
        @case('arrow') <path d="M5 12h14m-5-5 5 5-5 5"/> @break
        @case('menu') <path d="M4 6h16M4 12h16M4 18h16"/> @break
        @case('close') <path d="m6 6 12 12M6 18 18 6"/> @break
        @case('logout') <path d="M9 4H4v16h5m5-13 5 5-5 5M8 12h11"/> @break
        @default <rect x="5" y="3" width="14" height="18" rx="2"/><path d="M9 8h6M9 12h6M9 16h3"/>
    @endswitch
</svg>
