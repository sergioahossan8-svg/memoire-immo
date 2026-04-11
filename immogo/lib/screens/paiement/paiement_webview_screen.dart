// lib/screens/paiement/paiement_webview_screen.dart
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:webview_flutter/webview_flutter.dart';
import '../../core/theme/app_theme.dart';

class PaiementWebviewScreen extends StatefulWidget {
  final String url;
  final int? contratId;

  const PaiementWebviewScreen({
    super.key,
    required this.url,
    this.contratId,
  });

  @override
  State<PaiementWebviewScreen> createState() =>
      _PaiementWebviewScreenState();
}

class _PaiementWebviewScreenState extends State<PaiementWebviewScreen> {
  late final WebViewController _controller;
  bool _isLoading = true;
  String _title = 'Paiement sécurisé';

  @override
  void initState() {
    super.initState();
    _controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setNavigationDelegate(NavigationDelegate(
        onPageStarted: (_) => setState(() => _isLoading = true),
        onPageFinished: (url) {
          setState(() => _isLoading = false);
          _checkCallbackUrl(url);
        },
        onWebResourceError: (error) {
          setState(() => _isLoading = false);
        },
        onNavigationRequest: (request) {
          // Intercepter les redirections de retour FedaPay
          if (_isReturnUrl(request.url)) {
            _handleReturnUrl(request.url);
            return NavigationDecision.prevent;
          }
          return NavigationDecision.navigate;
        },
      ))
      ..loadRequest(Uri.parse(widget.url));
  }

  bool _isReturnUrl(String url) {
    return url.contains('/paiement/retour') ||
        url.contains('/historique') ||
        url.contains('status=approved') ||
        url.contains('status=declined') ||
        url.contains('status=cancelled');
  }

  void _checkCallbackUrl(String url) {
    if (url.contains('status=approved') ||
        url.contains('/historique')) {
      _handleSuccess();
    } else if (url.contains('status=declined') ||
        url.contains('status=cancelled')) {
      _handleFailure();
    }
  }

  void _handleReturnUrl(String url) {
    if (url.contains('status=approved')) {
      _handleSuccess();
    } else {
      _handleFailure();
    }
  }

  void _handleSuccess() {
    if (!mounted) return;
    context.go('/paiement/confirmation?success=true');
  }

  void _handleFailure() {
    if (!mounted) return;
    context.go('/paiement/confirmation?success=false');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(_title),
        leading: IconButton(
          icon: const Icon(Icons.close),
          onPressed: () => _showCancelDialog(),
        ),
      ),
      body: Stack(
        children: [
          WebViewWidget(controller: _controller),
          if (_isLoading)
            Container(
              color: Colors.white,
              child: const Center(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    CircularProgressIndicator(color: AppColors.primary),
                    SizedBox(height: 16),
                    Text('Chargement du paiement...'),
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
            'Voulez-vous vraiment annuler cette transaction ? Aucun paiement ne sera effectué.'),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(context, false),
              child: const Text('Continuer')),
          TextButton(
              onPressed: () => Navigator.pop(context, true),
              child: const Text('Annuler',
                  style: TextStyle(color: AppColors.error))),
        ],
      ),
    );
    if (confirm == true && mounted) {
      context.go('/');
    }
  }
}
