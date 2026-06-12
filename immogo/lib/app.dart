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
import 'screens/notifications/notifications_screen.dart';

/// Clé globale pour naviguer depuis n'importe où (ex: logout)
final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

bool _isPublicRoute(String loc) {
  const publicExact = {
    '/', '/rechercher', '/login', '/register', '/forgot-password',
    '/splash', '/estimation',
  };
  if (publicExact.contains(loc)) return true;
  if (loc.startsWith('/biens/')) return true;
  if (loc.startsWith('/paiement/confirmation')) return true;
  if (loc.startsWith('/reset-password')) return true;
  return false;
}

class _AuthListenable extends ChangeNotifier {
  AuthState _state;
  _AuthListenable(this._state);
  AuthState get state => _state;
  void update(AuthState s) { _state = s; notifyListeners(); }
}

final _authListenableProvider = Provider<_AuthListenable>((ref) {
  final l = _AuthListenable(ref.read(authProvider));
  ref.listen<AuthState>(authProvider, (_, next) => l.update(next));
  return l;
});

final routerProvider = Provider<GoRouter>((ref) {
  final auth = ref.read(_authListenableProvider);

  return GoRouter(
    navigatorKey: navigatorKey,
    initialLocation: '/splash',
    refreshListenable: auth,
    redirect: (context, state) {
      final isLoading = auth.state.status == AuthStatus.loading;
      final isAuth    = auth.state.status == AuthStatus.authenticated;
      final loc       = state.matchedLocation;
      final onAuth    = loc == '/login' || loc == '/register'
                     || loc == '/forgot-password'
                     || loc.startsWith('/reset-password');
      final onSplash  = loc == '/splash';

      if (isLoading) return (onSplash || onAuth) ? null : '/splash';
      if (isAuth && (onSplash || onAuth)) return '/';
      if (!isAuth && onSplash) return '/';
      // Après logout depuis /profil → accueil (liste des biens)
      // Autres pages protégées → login
      if (!isAuth && !_isPublicRoute(loc)) {
        if (loc == '/profil' || loc == '/favoris' || loc == '/historique'
            || loc == '/notifications' || loc == '/rechercher') return '/';
        return '/login';
      }
      return null;
    },
    routes: [
      GoRoute(path: '/splash',        builder: (_, __) => const SplashScreen()),
      GoRoute(path: '/login',         builder: (_, __) => const LoginScreen()),
      GoRoute(path: '/register',      builder: (_, __) => const RegisterScreen()),
      GoRoute(path: '/forgot-password', builder: (_, __) => const ForgotPasswordScreen()),
      GoRoute(
        path: '/reset-password',
        builder: (_, s) => ResetPasswordScreen(
          token: s.uri.queryParameters['token'] ?? '',
          email: s.uri.queryParameters['email'] ?? '',
        ),
      ),
      GoRoute(path: '/estimation', builder: (_, __) => const EstimationScreen()),
      GoRoute(
        path: '/paiement/webview',
        builder: (_, s) => PaiementWebviewScreen(
          url: s.uri.queryParameters['url'] ?? '',
          contratId: int.tryParse(s.uri.queryParameters['contrat_id'] ?? ''),
        ),
      ),
      GoRoute(
        path: '/paiement/kkiapay',
        builder: (_, s) => KkiapayScreen(data: (s.extra as Map<String, dynamic>?) ?? {}),
      ),
      GoRoute(
        path: '/paiement/confirmation',
        builder: (_, s) => PaiementConfirmationScreen(
          reference: s.uri.queryParameters['reference'],
          success:   s.uri.queryParameters['success'] == 'true',
        ),
      ),
      ShellRoute(
        builder: (context, state, child) => AppShell(child: child),
        routes: [
          GoRoute(path: '/',              builder: (_, __) => const BiensListScreen()),
          GoRoute(path: '/rechercher',    builder: (_, __) => const BiensListScreen()),
          GoRoute(path: '/favoris',       builder: (_, __) => const FavorisScreen()),
          GoRoute(path: '/historique',    builder: (_, __) => const HistoriqueScreen()),
          GoRoute(path: '/notifications', builder: (_, __) => const NotificationsScreen()),
          GoRoute(path: '/profil',        builder: (_, __) => const ProfilScreen()),
          GoRoute(
            path: '/biens/:id',
            builder: (_, s) => BienDetailScreen(
                id: int.parse(s.pathParameters['id']!)),
          ),
          GoRoute(
            path: '/reservation/:bienId',
            builder: (_, s) => ReservationScreen(
              bienId: int.parse(s.pathParameters['bienId']!),
              payerComplet: s.uri.queryParameters['type'] == 'complet',
            ),
          ),
          GoRoute(
            path: '/contrats/:id',
            builder: (_, s) =>
                ContratDetailScreen(id: int.parse(s.pathParameters['id']!)),
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
    return MaterialApp.router(
      title: 'ImmoGo Bénin',
      theme: AppTheme.lightTheme,
      routerConfig: ref.watch(routerProvider),
      debugShowCheckedModeBanner: false,
    );
  }
}
