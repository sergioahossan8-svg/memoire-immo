// lib/screens/paiement/kkiapay_screen.dart
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:webview_flutter/webview_flutter.dart';
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

    // HTML avec le SDK KKiapay chargé en HTTPS
    final html = '''<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>Paiement KKiapay</title>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      background:#f5f5f5;
      display:flex; align-items:center; justify-content:center;
      min-height:100vh; padding:20px;
    }
    .card {
      background:white; border-radius:20px; padding:28px;
      max-width:380px; width:100%; text-align:center;
      box-shadow:0 4px 24px rgba(0,0,0,0.10);
    }
    .logo-text { font-size:26px; font-weight:800; color:#f0522e; letter-spacing:-1px; }
    .secure { font-size:12px; color:#999; margin-bottom:20px; }
    .amount { font-size:34px; font-weight:800; color:#1a1a2e; margin:12px 0 4px; }
    .currency { font-size:16px; color:#666; margin-bottom:8px; }
    .desc { color:#888; margin-bottom:24px; font-size:13px; line-height:1.5; }
    #pay-btn {
      background:#f0522e; color:white; border:none;
      padding:16px 0; border-radius:14px; font-size:16px;
      font-weight:700; cursor:pointer; width:100%;
      transition: background 0.2s;
    }
    #pay-btn:active { background:#c93d1e; }
    #pay-btn:disabled { background:#ccc; cursor:not-allowed; }
    #status { color:#888; font-size:13px; margin-top:14px; min-height:20px; }
    .spinner {
      display:none; width:20px; height:20px;
      border:2px solid #f0522e; border-top-color:transparent;
      border-radius:50%; animation:spin .7s linear infinite;
      margin:12px auto 0;
    }
    @keyframes spin { to { transform:rotate(360deg); } }
  </style>
</head>
<body>
<div class="card">
  <div class="logo-text">KK<span style="color:#1a1a2e">iapay</span></div>
  <div class="secure">Paiement sécurisé</div>
  <div class="amount">$montantFmt</div>
  <div class="currency">FCFA</div>
  <div class="desc">$descEscaped</div>
  <button id="pay-btn" onclick="doPay()">Payer maintenant</button>
  <div id="status"></div>
  <div class="spinner" id="spinner"></div>
</div>

<script src="https://cdn.kkiapay.me/k.js"></script>
<script>
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
      btn.textContent = 'Réessayer';
      document.getElementById('spinner').style.display = 'none';
      setStatus('Erreur: ' + e.message);
    }
  }

  addSuccessListener(function(response) {
    if (paid) return;
    paid = true;
    setStatus('Paiement réussi ! Confirmation en cours...');
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
      ))
      ..loadHtmlString(html, baseUrl: 'https://cdn.kkiapay.me');
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
      appBar: AppBar(
        title: const Text('Paiement sécurisé'),
        leading: IconButton(
          icon: const Icon(Icons.close),
          onPressed: _showCancelDialog,
        ),
      ),
      body: Stack(
        children: [
          WebViewWidget(controller: _controller),
          if (_isLoading || _confirming)
            Container(
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
