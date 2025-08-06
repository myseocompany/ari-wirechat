@extends('layout')

@section('content')

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.tailwindcss.com"></script>

<div class="flex min-h-screen">

    @include('actions.sidebar')

    @include('actions.main')

    @include('actions.modal_pending', ['action_options' => $action_options, 'statuses_options' => $statuses_options])

</div>

@endsection
