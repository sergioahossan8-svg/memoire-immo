// lib/screens/paiement/kkiapay_screen.dart
import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:webview_flutter/webview_flutter.dart';
import 'package:webview_flutter_android/webview_flutter_android.dart';
import 'package:dio/dio.dart';
import '../../core/theme/app_theme.dart';
import '../../services/api_service.dart';
import '../../core/constants/api_constants.dart';

class KkiapayScreen extends StatefulWidget {
  final Map<String, dynamic> data;

  const KkiapayScreen({super.key, required this.data});

  @override
  State<KkiapayScreen> createState() => _KkiapayScreenState();
}

class _KkiapayScreenState extends State<KkiapayScreen> {
  late final WebViewController _controller;
  bool _isLoading = true;
  bool _confirming = false;

  // Formatage montant sans regex interpolée
  String _formatMontant(int montant) {
    final s = montant.toString();
    final buffer = StringBuffer();
    final offset = s.length % 3;
    for (int i = 0; i < s.length; i++) {
      if (i > 0 && (i - offset) % 3 == 0) buffer.write(' ');
      buffer.write(s[i]);
    }
    return buffer.toString();
  }

  @override
  void initState() {
    super.initState();
    _initWebView();
  }

  void _initWebView() {
    final publicKey  = widget.data['kkiapay_public_key'] as String? ?? '';
    final sandbox    = widget.data['kkiapay_sandbox'] as bool? ?? true;
    final montant    = (widget.data['montant'] as num?)?.toInt() ?? 0;
    final description = widget.data['description'] as String? ?? '';
    final reference  = widget.data['reference'] as String? ?? '';
    final email      = widget.data['client_email'] as String? ?? '';
    final phone      = widget.data['client_phone'] as String? ?? '';
    final nom        = (widget.data['client_nom'] as String? ?? '')
        .replaceAll('"', '&quot;');

    final sandboxStr   = sandbox ? 'true' : 'false';
    final montantFmt   = _formatMontant(montant);
    final descEscaped  = description
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('"', '&quot;');

    // HTML avec le SDK KKiapay chargé en HTTPS — optimisé pour petit écran émulateur
    final html = '''<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
  <title>Paiement KKiapay</title>
  <style>
    *, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
    html, body {
      width:100%; height:100%;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background:#f0f4f8;
      overflow-x: hidden;
    }
    body {
      display:flex;
      align-items: flex-start;
      justify-content:center;
      min-height: 100vh;
      padding: 12px 12px 24px;
    }
    .card {
      background:white;
      border-radius:16px;
      padding: 20px 16px;
      width:100%;
      max-width:420px;
      text-align:center;
      box-shadow:0 2px 16px rgba(0,0,0,0.10);
    }
    .logo-text {
      font-size: 22px;
      font-weight: 800;
      color: #f0522e;
      letter-spacing: -0.5px;
      margin-bottom: 2px;
    }
    .secure {
      font-size: 11px;
      color: #999;
      margin-bottom: 14px;
    }
    .divider { border:none; border-top:1px solid #eee; margin: 12px 0; }
    .amount {
      font-size: 28px;
      font-weight: 800;
      color: #1a1a2e;
      margin: 0;
      line-height: 1.2;
    }
    .currency {
      font-size: 13px;
      color: #666;
      margin-bottom: 6px;
    }
    .desc {
      color: #888;
      margin-bottom: 16px;
      font-size: 12px;
      line-height: 1.5;
      padding: 0 4px;
    }
    #pay-btn {
      background:#f0522e;
      color:white;
      border:none;
      padding: 14px 0;
      border-radius:12px;
      font-size: 15px;
      font-weight:700;
      cursor:pointer;
      width:100%;
      transition: background 0.2s, transform 0.1s;
    }
    #pay-btn:active { background:#c93d1e; transform: scale(0.98); }
    #pay-btn:disabled { background:#ccc; cursor:not-allowed; transform:none; }
    #status {
      color: #666;
      font-size: 12px;
      margin-top: 10px;
      min-height: 18px;
      padding: 0 4px;
    }
    .spinner {
      display:none;
      width: 18px;
      height: 18px;
      border: 2px solid #f0522e;
      border-top-color: transparent;
      border-radius:50%;
      animation: spin .7s linear infinite;
      margin: 10px auto 0;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
    /* Assure que le widget popup KKiapay est au-dessus */
    iframe, .kkiapay-iframe, [class*="kkiapay"] {
      z-index: 9999 !important;
      max-width: 100vw !important;
    }
  </style>
</head>
<body>
<div class="card">
  <div class="logo-text">KK<span style="color:#1a1a2e">iapay</span></div>
  <div class="secure">Paiement 100% sécurisé</div>
  <hr class="divider">
  <div class="amount">$montantFmt</div>
  <div class="currency">FCFA</div>
  <div class="desc">$descEscaped</div>
  <button id="pay-btn" onclick="doPay()">Payer maintenant</button>
  <div id="status"></div>
  <div class="spinner" id="spinner"></div>
</div>

<script src="https://cdn.kkiapay.me/k.js"></script>
<script>
  // Forcer window.open → navigation inline (émulateur Android WebView)
  var _origOpen = window.open;
  window.open = function(url, name, features) {
    if (url) { window.location.href = url; return window; }
    return _origOpen ? _origOpen.call(window, url, name, features) : null;
  };

  var paid = false;

  function setStatus(msg) {
    document.getElementById('status').textContent = msg;
  }

  function doPay() {
    if (paid) return;
    var btn = document.getElementById('pay-btn');
    btn.disabled = true;
    btn.textContent = 'Ouverture...';
    document.getElementById('spinner').style.display = 'block';
    setStatus('');

    try {
      openKkiapayWidget({
        amount: $montant,
        api_key: "$publicKey",
        sandbox: $sandboxStr,
        phone: "$phone",
        email: "$email",
        name: "$nom",
        data: "$reference"
      });
    } catch(e) {
      btn.disabled = false;
      btn.textContent = 'Payer maintenant';
      document.getElementById('spinner').style.display = 'none';
      setStatus('Erreur : ' + e.message);
    }
  }

  addSuccessListener(function(response) {
    if (paid) return;
    paid = true;
    document.getElementById('pay-btn').textContent = 'Paiement confirmé ✓';
    setStatus('Confirmation en cours...');
    document.getElementById('spinner').style.display = 'block';
    KkiapayFlutter.postMessage(JSON.stringify({
      event: 'success',
      transactionId: response.transactionId
    }));
  });

  addFailedListener(function(response) {
    var btn = document.getElementById('pay-btn');
    btn.disabled = false;
    btn.textContent = 'Réessayer';
    document.getElementById('spinner').style.display = 'none';
    setStatus('Paiement échoué. Veuillez réessayer.');
    KkiapayFlutter.postMessage(JSON.stringify({ event: 'failed' }));
  });
</script>
</body>
</html>''';

    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setBackgroundColor(Colors.transparent)
      ..addJavaScriptChannel(
        'KkiapayFlutter',
        onMessageReceived: (msg) => _onKkiapayMessage(msg.message),
      )
      ..setNavigationDelegate(NavigationDelegate(
        onPageStarted: (_) => setState(() => _isLoading = true),
        onPageFinished: (_) => setState(() => _isLoading = false),
        onWebResourceError: (err) {
          debugPrint('WebView error: ${err.description}');
          setState(() => _isLoading = false);
        },
        onNavigationRequest: (request) {
          return NavigationDecision.navigate;
        },
      ))
      ..loadHtmlString(html, baseUrl: 'https://cdn.kkiapay.me');

    // Configuration spécifique Android (émulateur + téléphone physique)
    if (defaultTargetPlatform == TargetPlatform.android) {
      final androidController =
          _controller.platform as AndroidWebViewController;
      androidController.setMediaPlaybackRequiresUserGesture(false);
    }
  }

  void _onKkiapayMessage(String message) async {
    try {
      final data = jsonDecode(message) as Map<String, dynamic>;
      final event = data['event'] as String?;
      final transactionId = data['transactionId']?.toString();

      if (event == 'success' && transactionId != null) {
        await _confirmerPaiement(transactionId);
      } else if (event == 'failed') {
        // L'utilisateur peut réessayer — pas de navigation automatique
      }
    } catch (_) {}
  }

  Future<void> _confirmerPaiement(String transactionId) async {
    if (_confirming) return;
    setState(() => _confirming = true);

    try {
      final dio = ApiService().dio;
      final response = await dio.post(
        ApiConstants.confirmerKkiapay,
        data: {
          'transaction_id': transactionId,
          'pending_key': widget.data['pending_key'],
        },
      );
      final ref = response.data?['reference']
          ?? widget.data['reference']
          ?? '';
      if (mounted) {
        context.go('/paiement/confirmation?success=true&reference=$ref');
      }
    } on DioException catch (e) {
      final msg = e.response?.data?['message'] ?? 'Erreur de confirmation.';
      if (mounted) {
        setState(() => _confirming = false);
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(msg),
            backgroundColor: AppColors.error,
            action: SnackBarAction(
              label: 'OK',
              textColor: Colors.white,
              onPressed: () {},
            ),
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF0F4F8),
      appBar: AppBar(
        title: const Text('Paiement sécurisé'),
        leading: IconButton(
          icon: const Icon(Icons.close),
          onPressed: _showCancelDialog,
        ),
      ),
      body: Stack(
        children: [
          // WebView en plein écran
          Positioned.fill(
            child: WebViewWidget(controller: _controller),
          ),
          if (_isLoading || _confirming)
            Positioned.fill(
              child: Container(
                color: Colors.white,
                child: Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const CircularProgressIndicator(color: AppColors.primary),
                      const SizedBox(height: 16),
                      Text(
                        _confirming
                            ? 'Confirmation du paiement...'
                            : 'Chargement...',
                        style: const TextStyle(color: AppColors.textSecondary),
                      ),
                    ],
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }

  Future<void> _showCancelDialog() async {
    final confirm = await showDialog<bool>(
      context: context,
      builder: (_) => AlertDialog(
        title: const Text('Annuler le paiement ?'),
        content: const Text(
            'Aucun paiement ne sera effectué si vous annulez maintenant.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context, false),
            child: const Text('Continuer'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context, true),
            child: const Text('Annuler',
                style: TextStyle(color: AppColors.error)),
          ),
        ],
      ),
    );
    if (confirm == true && mounted) {
      context.go('/');
    }
  }
}
