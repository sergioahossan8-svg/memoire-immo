// lib/app.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'core/theme/app_theme.dart';
import 'providers/auth_provider.dart';
import 'screens/splash/splash_screen.dart';
import 'screens/auth/login_screen.dart';
import 'screens/auth/register_screen.dart';
import 'screens/home/home_screen.dart';
import 'screens/biens/bien_detail_screen.dart';
import 'screens/biens/estimation_screen.dart';
import 'screens/contrats/reservation_screen.dart';
import 'screens/contrats/contrat_detail_screen.dart';
import 'screens/paiement/paiement_webview_screen.dart';
import 'screens/paiement/paiement_confirmation_screen.dart';
import 'screens/paiement/kkiapay_screen.dart';

final routerProvider = Provider<GoRouter>((ref) {
  final authState = ref.watch(authProvider);

  return GoRouter(
    initialLocation: '/splash',
    redirect: (context, state) {
      final isLoading = authState.status == AuthStatus.loading;
      final isAuth = authState.status == AuthStatus.authenticated;
      final loc = state.matchedLocation;
      final onAuthPages = loc == '/login' || loc == '/register';
      final onSplash = loc == '/splash';

      // Si en chargement, rester sur splash
      if (isLoading && !onSplash) {
        return '/splash';
      }
      
      // Si authentifié et sur splash/login/register, aller à l'accueil
      if (isAuth && (onSplash || onAuthPages)) {
        return '/';
      }
      
      // Si non authentifié et pas sur une page publique, aller au login
      if (!isAuth && !onAuthPages && !onSplash) {
        return '/login';
      }
      
      return null;
    },
    routes: [
      GoRoute(
          path: '/splash',
          builder: (_, __) => const SplashScreen()),
      GoRoute(
          path: '/login',
          builder: (_, __) => const LoginScreen()),
      GoRoute(
          path: '/register',
          builder: (_, __) => const RegisterScreen()),
      GoRoute(path: '/', builder: (_, __) => const HomeScreen()),
      GoRoute(
        path: '/biens/:id',
        builder: (_, state) =>
            BienDetailScreen(id: int.parse(state.pathParameters['id']!)),
      ),
      GoRoute(
          path: '/estimation',
          builder: (_, __) => const EstimationScreen()),
      GoRoute(
        path: '/reservation/:bienId',
        builder: (_, state) => ReservationScreen(
            bienId: int.parse(state.pathParameters['bienId']!)),
      ),
      GoRoute(
        path: '/contrats/:id',
        builder: (_, state) =>
            ContratDetailScreen(id: int.parse(state.pathParameters['id']!)),
      ),
      GoRoute(
        path: '/paiement/webview',
        builder: (_, state) => PaiementWebviewScreen(
            url: state.uri.queryParameters['url'] ?? '',
            contratId: state.uri.queryParameters['contrat_id'] != null
                ? int.tryParse(state.uri.queryParameters['contrat_id']!)
                : null),
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
          success: state.uri.queryParameters['success'] == 'true',
        ),
      ),
    ],
  );
});

class ImmoGoApp extends ConsumerWidget {
  const ImmoGoApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final router = ref.watch(routerProvider);
    return MaterialApp.router(
      title: 'ImmoGo Bénin',
      theme: AppTheme.lightTheme,
      routerConfig: router,
      debugShowCheckedModeBanner: false,
    );
  }
}
