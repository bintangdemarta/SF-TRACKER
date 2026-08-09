@props([
    'title' => config('app.name'),
    'description' => '',
    'canonical' => url()->current(),
    'ogImage' => asset('images/og-cover.png'),
    'ogType' => 'website',
])
@php
    $trimmedDescription = \Illuminate\Support\Str::limit($description, 157, '...');
@endphp

<title>{{ $title }}</title>
<meta name="description" content="{{ $trimmedDescription }}">
<link rel="canonical" href="{{ $canonical }}">
<meta name="robots" content="index, follow">

<meta property="og:type" content="{{ $ogType }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $trimmedDescription }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:locale" content="id_ID">

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $trimmedDescription }}">
<meta name="twitter:image" content="{{ $ogImage }}">
