@extends('layouts.app')

@section('title', 'Paiement - ImmoGo')

@section('content')
<div class="max-w-2xl mx-auto px-4 py-8">
    <div class="card p-8">
        <h1 class="text-2xl font-bold text-gray-800 mb-1">Paiement sécurisé</h1>
        <p class="text-gray-500 text-sm mb-6">Vous allez payer via KKiapay — Mobile Money (MTN, Moov) et carte bancaire.</p>

        <div class="bg-gray-50 rounded-xl p-5 mb-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-gray-500 text-sm">Description</span>
                <span class="text-gray-800 text-sm font-medium">{{ $description }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-gray-500 text-sm">Montant</span>
                <span class="text-2xl font-bold text-cyan-500">{{ number_format($montant, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>

        <div class="flex items-center gap-3 bg-blue-50 rounded-xl p-4 mb-6">
            <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <i class="fas fa-shield-alt text-blue-500"></i>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-800">Paiement sécurisé via KKiapay</p>
                <p class="text-xs text-gray-500">MTN Mobile Money, Moov Money, Visa, Mastercard</p>
            </div>
        </div>

        <form id="confirmForm" method="POST" action="{{ route('paiement.kkiapay.confirmer') }}">
            @csrf
            <input type="hidden" name="transaction_id" id="kkiapayTransactionId">
            <input type="hidden" name="pending_key" value="{{ $pendingKey }}">
        </form>

        <button id="payBtn" onclick="lancerKkiapay()" class="btn-primary w-full text-lg py-4">
            <i class="fas fa-lock"></i>
            Payer {{ number_format($montant, 0, ',', ' ') }} FCFA avec KKiapay
        </button>

        <a href="{{ url()->previous() }}" class="btn-secondary w-full text-center block mt-3">Annuler</a>

        <div id="errorMsg" class="hidden mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm">
            <i class="fas fa-exclamation-circle mr-1"></i>
            <span id="errorText"></span>
        </div>
    </div>
</div>

<script src="https://cdn.kkiapay.me/k.js"></script>

@push('scripts')
<script>
    const KKIAPAY_KEY  = "{{ $kkiapayPublicKey ?? config('services.kkiapay.public_key') }}";
    const MONTANT      = {{ (int) $montant }};
    const SANDBOX      = {{ ($kkiapaySandbox ?? config('services.kkiapay.sandbox', true)) ? 'true' : 'false' }};
    const CLIENT_EMAIL = "{{ auth()->user()->email }}";
    const CLIENT_PHONE = "{{ preg_replace('/\D/', '', auth()->user()->telephone ?? '') }}";
    const CLIENT_NOM   = "{{ auth()->user()->prenom }} {{ auth()->user()->name }}";

    function ouvrirWidgetKkiapay() {
        if (!KKIAPAY_KEY || KKIAPAY_KEY.length < 10) {
            document.getElementById('errorText').textContent = 'Clé KKiapay non configurée. Veuillez contacter l\'administrateur.';
            document.getElementById('errorMsg').classList.remove('hidden');
            return;
        }
        // Attendre que le SDK soit prêt si le script加载较慢
        const essayer = () => {
            if (typeof openKkiapayWidget !== 'function') {
                setTimeout(essayer, 200);
                return;
            }
            openKkiapayWidget({
                amount:  MONTANT,
                api_key: KKIAPAY_KEY,
                sandbox: SANDBOX,
                email:   CLIENT_EMAIL,
                phone:   CLIENT_PHONE,
                name:    CLIENT_NOM,
                currency: 'XOF',
                callback_url: "{{ route('paiement.retour') }}",
            });
        };
        essayer();
    }

    // 兼容两种事件机制：函数监听与自定义事件
    if (typeof addSuccessListener === 'function') {
        addSuccessListener(function(response) {
            document.getElementById('kkiapayTransactionId').value = response.transactionId;
            document.getElementById('confirmForm').submit();
        });
        addFailedListener(function(error) {
            document.getElementById('errorText').textContent = 'Paiement échoué : ' + (error.message || 'Veuillez réessayer.');
            document.getElementById('errorMsg').classList.remove('hidden');
        });
    } else {
        window.addEventListener('kkiapay.success', function(e) {
            const response = e.detail || {};
            document.getElementById('kkiapayTransactionId').value = response.transactionId;
            document.getElementById('confirmForm').submit();
        });
        window.addEventListener('kkiapay.fail', function(e) {
            const error = e.detail || {};
            document.getElementById('errorText').textContent = 'Paiement échoué : ' + (error.message || 'Veuillez réessayer.');
            document.getElementById('errorMsg').classList.remove('hidden');
        });
    }

    // 绑定按钮点击
    document.getElementById('payBtn').addEventListener('click', function(ev) {
        ev.preventDefault();
        ouvrirWidgetKkiapay();
    });
</script>
@endpush
@endsection
