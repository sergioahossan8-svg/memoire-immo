// lib/screens/splash/splash_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/theme/app_theme.dart';
import '../../providers/auth_provider.dart';

class SplashScreen extends ConsumerStatefulWidget {
  const SplashScreen({super.key});

  @override
  ConsumerState<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends ConsumerState<SplashScreen> {
  bool _hasNavigated = false;

  @override
  void initState() {
    super.initState();
    _init();
  }

  Future<void> _init() async {
    if (_hasNavigated) return;
    
    print('🚀 Splash: Initialisation...');
    await Future.delayed(const Duration(milliseconds: 300));
    print('🔐 Splash: Vérification auth...');
    try {
      await ref.read(authProvider.notifier).checkAuth();
      print('✅ Splash: checkAuth terminé');
      
      // Navigation forcée après checkAuth
      if (mounted && !_hasNavigated) {
        _hasNavigated = true;
        final authState = ref.read(authProvider);
        print('🧭 Splash: Navigation vers ${authState.status}');
        if (authState.status == AuthStatus.authenticated) {
          context.go('/');
        } else if (authState.status == AuthStatus.unauthenticated) {
          context.go('/login');
        }
      }
    } catch (e) {
      print('❌ Splash: Erreur checkAuth: $e');
      if (mounted && !_hasNavigated) {
        _hasNavigated = true;
        context.go('/login');
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.primary,
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.home_work_rounded, size: 90, color: Colors.white),
            const SizedBox(height: 20),
            Text(
              'ImmoGo',
              style: Theme.of(context).textTheme.displayLarge?.copyWith(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                  ),
            ),
            const SizedBox(height: 8),
            Text(
              'Votre agence immobilière au Bénin',
              style: Theme.of(context)
                  .textTheme
                  .bodyLarge
                  ?.copyWith(color: Colors.white70),
            ),
            const SizedBox(height: 60),
            const CircularProgressIndicator(
                color: Colors.white, strokeWidth: 2),
          ],
        ),
      ),
    );
  }
}
