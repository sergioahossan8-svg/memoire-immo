@extends('layouts.app')

@section('title', 'Notifications - ImmoGo')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold text-gray-800 mb-6">Notifications</h1>

    @forelse($notifications as $notif)
        <div class="card p-4 mb-3 flex items-start gap-4 {{ !$notif->lu ? 'border-l-4 border-cyan-400' : '' }}">
            <div class="w-10 h-10 bg-cyan-100 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="fas fa-bell text-cyan-500 text-sm"></i>
            </div>
            <div class="flex-1">
                <p class="font-semibold text-gray-800 text-sm">{{ $notif->titre }}</p>
                <p class="text-gray-500 text-sm mt-0.5">{{ $notif->message }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
            </div>
            @if($notif->lien)
                <a href="{{ $notif->lien }}" class="text-xs text-cyan-500 hover:underline whitespace-nowrap">Voir</a>
            @endif
        </div>
    @empty
        <div class="text-center py-16">
            <i class="fas fa-bell text-5xl text-gray-200 mb-4"></i>
            <p class="text-gray-400">Aucune notification pour le moment.</p>
        </div>
    @endforelse

    <div class="mt-4">{{ $notifications->links() }}</div>
</div>
@endsection
