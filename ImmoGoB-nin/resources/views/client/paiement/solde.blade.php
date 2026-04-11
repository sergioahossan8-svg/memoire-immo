@extends('layouts.app')

@section('title', 'Paiement - ImmoGo')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="card p-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">Paiement</h1>
        <p class="text-gray-500 text-sm mb-6">Vous allez être redirigé vers FedaPay pour effectuer votre paiement en toute sécurité.</p>

        {{-- Récap montants --}}
        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-gray-50 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-400 mb-1">Montant total</p>
                <p class="font-bold text-gray-800 text-sm">{{ number_format($contrat->getMontantTotal(), 0, ',', ' ') }} FCFA</p>
            </div>
            <div class="bg-green-50 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-400 mb-1">Déjà payé</p>
                <p class="font-bold text-green-600 text-sm">{{ number_format($montantPaye, 0, ',', ' ') }} FCFA</p>
            </div>
            <div class="bg-orange-50 rounded-xl p-4 text-center">
                <p class="text-xs text-gray-400 mb-1">Solde restant</p>
                <p class="font-bold text-orange-600 text-sm">{{ number_format($solde, 0, ',', ' ') }} FCFA</p>
            </div>
        </div>

        @if(session('error') || $errors->has('fedapay'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm mb-4">
                <i class="fas fa-exclamation-circle mr-1"></i>
                {{ session('error') ?? $errors->first('fedapay') }}
            </div>
        @endif

        <form method="POST" action="{{ route('client.payer.solde.post', $contrat) }}" class="space-y-4">
            @csrf

            <div>
                <label class="form-label">Type de paiement</label>
                <select name="type_paiement" class="form-input" required>
                    @if($montantPaye == 0)
                        <option value="acompte">Acompte 10% — {{ number_format($montantAcompte, 0, ',', ' ') }} FCFA</option>
                    @endif
                    <option value="solde">Solde restant — {{ number_format($solde, 0, ',', ' ') }} FCFA</option>
                </select>
            </div>

            <div>
                <label class="form-label">Montant à payer (FCFA)</label>
                <input type="number" name="montant" id="montantInput"
                    class="form-input"
                    value="{{ $montantPaye == 0 ? $montantAcompte : $solde }}"
                    min="1" required>
            </div>

            {{-- FedaPay badge --}}
            <div class="flex items-center gap-3 bg-blue-50 rounded-xl p-4">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-shield-alt text-blue-500"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">Paiement sécurisé via FedaPay</p>
                    <p class="text-xs text-gray-500">Mobile Money (MTN, Moov), carte bancaire et plus</p>
                </div>
            </div>

            <button type="submit" class="btn-primary w-full">
                <i class="fas fa-lock"></i> Payer maintenant avec FedaPay
            </button>
            <a href="{{ route('client.historique') }}" class="btn-secondary w-full text-center block">Annuler</a>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.querySelector('select[name="type_paiement"]').addEventListener('change', function() {
    const montantInput = document.getElementById('montantInput');
    if (this.value === 'acompte') {
        montantInput.value = {{ $montantAcompte }};
    } else {
        montantInput.value = {{ $solde }};
    }
});
</script>
@endpush
@endsection
