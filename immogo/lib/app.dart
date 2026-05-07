// lib/app.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'core/theme/app_theme.dart';
import 'providers/auth_provider.dart';
import 'screens/splash/splash_screen.dart';
import 'screens/auth/login_screen.dart';
import 'screens/auth/register_screen.dart';
import 'screens/auth/forgot_password_screen.dart';
import 'screens/auth/reset_password_screen.dart';
import 'screens/home/app_shell.dart';
import 'screens/biens/biens_list_screen.dart';
import 'screens/biens/bien_detail_screen.dart';
import 'screens/biens/estimation_screen.dart';
import 'screens/favoris/favoris_screen.dart';
import 'screens/contrats/reservation_screen.dart';
import 'screens/contrats/contrat_detail_screen.dart';
import 'screens/contrats/historique_screen.dart';
import 'screens/profil/profil_screen.dart';
import 'screens/paiement/paiement_webview_screen.dart';
import 'screens/paiement/paiement_confirmation_screen.dart';
import 'screens/paiement/kkiapay_screen.dart';

/// Routes publiques (accessibles sans connexion)
bool _isPublicRoute(String loc) {
  const publicExact = {
    '/', '/login', '/register', '/forgot-password', '/splash',
    '/estimation', '/favoris', '/historique', '/profil',
  };
  if (publicExact.contains(loc)) return true;
  if (loc.startsWith('/biens/')) return true;
  if (loc.startsWith('/paiement/confirmation')) return true;
  if (loc.startsWith('/reset-password')) return true;
  if (loc.startsWith('/contrats/')) return true;
  if (loc.startsWith('/reservation/')) return true;
  return false;
}

class _AuthNotifierListenable extends ChangeNotifier {
  AuthState _state;
  _AuthNotifierListenable(this._state);
  AuthState get state => _state;
  void update(AuthState newState) {
    _state = newState;
    notifyListeners();
  }
}

final _authListenableProvider = Provider<_AuthNotifierListenable>((ref) {
  final listenable = _AuthNotifierListenable(ref.read(authProvider));
  ref.listen<AuthState>(authProvider, (_, next) => listenable.update(next));
  return listenable;
});

final routerProvider = Provider<GoRouter>((ref) {
  final authListenable = ref.read(_authListenableProvider);

  return GoRouter(
    initialLocation: '/splash',
    refreshListenable: authListenable,
    redirect: (context, state) {
      final authState = authListenable.state;
      final isLoading = authState.status == AuthStatus.loading;
      final isAuth   = authState.status == AuthStatus.authenticated;
      final loc      = state.matchedLocation;

      final onAuthPages = loc == '/login'
          || loc == '/register'
          || loc == '/forgot-password'
          || loc.startsWith('/reset-password');
      final onSplash = loc == '/splash';

      if (isLoading) {
        if (onSplash || onAuthPages) return null;
        return '/splash';
      }

      // Authentifié sur splash/login/register → accueil
      if (isAuth && (onSplash || onAuthPages)) return '/';

      // Non authentifié sur splash → accueil
      if (!isAuth && onSplash) return '/';

      // Non authentifié sur page protégée → login
      if (!isAuth && !_isPublicRoute(loc)) return '/login';

      return null;
    },
    routes: [
      // ── Pages sans shell (plein écran) ──────────────────────────────────
      GoRoute(path: '/splash',   builder: (_, __) => const SplashScreen()),
      GoRoute(path: '/login',    builder: (_, __) => const LoginScreen()),
      GoRoute(path: '/register', builder: (_, __) => const RegisterScreen()),
      GoRoute(
        path: '/forgot-password',
        builder: (_, __) => const ForgotPasswordScreen(),
      ),
      GoRoute(
        path: '/reset-password',
        builder: (_, state) => ResetPasswordScreen(
          token: state.uri.queryParameters['token'] ?? '',
          email: state.uri.queryParameters['email'] ?? '',
        ),
      ),
      GoRoute(path: '/estimation', builder: (_, __) => const EstimationScreen()),
      GoRoute(
        path: '/paiement/webview',
        builder: (_, state) => PaiementWebviewScreen(
          url: state.uri.queryParameters['url'] ?? '',
          contratId: state.uri.queryParameters['contrat_id'] != null
              ? int.tryParse(state.uri.queryParameters['contrat_id']!)
              : null,
        ),
      ),
      GoRoute(
        path: '/paiement/kkiapay',
        builder: (_, state) => KkiapayScreen(
          data: (state.extra as Map<String, dynamic>?) ?? {},
        ),
      ),
      GoRoute(
        path: '/paiement/confirmation',
        builder: (_, state) => PaiementConfirmationScreen(
          reference: state.uri.queryParameters['reference'],
          success:   state.uri.queryParameters['success'] == 'true',
        ),
      ),

      // ── Pages avec shell (bottom nav visible) ───────────────────────────
      ShellRoute(
        builder: (context, state, child) => AppShell(child: child),
        routes: [
          GoRoute(path: '/',          builder: (_, __) => const BiensListScreen()),
          GoRoute(path: '/favoris',   builder: (_, __) => const FavorisScreen()),
          GoRoute(path: '/historique', builder: (_, __) => const HistoriqueScreen()),
          GoRoute(path: '/profil',    builder: (_, __) => const ProfilScreen()),
          GoRoute(
            path: '/biens/:id',
            builder: (_, state) =>
                BienDetailScreen(id: int.parse(state.pathParameters['id']!)),
          ),
          GoRoute(
            path: '/reservation/:bienId',
            builder: (_, state) {
              final bienId = int.parse(state.pathParameters['bienId']!);
              final type   = state.uri.queryParameters['type'];
              return ReservationScreen(
                bienId: bienId,
                payerComplet: type == 'complet',
              );
            },
          ),
          GoRoute(
            path: '/contrats/:id',
            builder: (_, state) =>
                ContratDetailScreen(id: int.parse(state.pathParameters['id']!)),
          ),
        ],
      ),
    ],
  );
});

class ImmoGoApp extends ConsumerWidget {
  const ImmoGoApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final router = ref.read(routerProvider);
    return MaterialApp.router(
      title: 'ImmoGo Bénin',
      theme: AppTheme.lightTheme,
      routerConfig: router,
      debugShowCheckedModeBanner: false,
    );
  }
}
