// lib/screens/splash/splash_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/theme/app_theme.dart';
import '../../providers/auth_provider.dart';

class SplashScreen extends ConsumerStatefulWidget {
  const SplashScreen({super.key});

  @override
  ConsumerState<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends ConsumerState<SplashScreen>
    with SingleTickerProviderStateMixin {
  late AnimationController _animCtrl;
  late Animation<double> _fadeAnim;
  late Animation<double> _scaleAnim;

  @override
  void initState() {
    super.initState();
    _animCtrl = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 700),
    );
    _fadeAnim = CurvedAnimation(parent: _animCtrl, curve: Curves.easeIn);
    _scaleAnim = Tween<double>(begin: 0.85, end: 1.0).animate(
      CurvedAnimation(parent: _animCtrl, curve: Curves.easeOutBack),
    );
    _animCtrl.forward();
    _init();
  }

  @override
  void dispose() {
    _animCtrl.dispose();
    super.dispose();
  }

  Future<void> _init() async {
    // Délai minimum pour l'animation
    await Future.delayed(const Duration(milliseconds: 800));

    if (!mounted) return;

    // Vérifier d'abord le token local (sans réseau)
    final authService = ref.read(authServiceProvider);
    final hasToken = await authService.hasLocalToken();

    if (!mounted) return;

    if (!hasToken) {
      // Pas de token → mettre l'état à unauthenticated, GoRouter redirige vers /login
      ref.read(authProvider.notifier).setUnauthenticated();
      return;
    }

    // Token présent → valider côté serveur avec timeout
    try {
      await ref.read(authProvider.notifier).checkAuth().timeout(
        const Duration(seconds: 15),
        onTimeout: () {
          // Timeout réseau → mode optimiste, on fait confiance au token local
          if (mounted) ref.read(authProvider.notifier).setAuthenticated();
        },
      );
    } catch (_) {
      if (mounted) {
        ref.read(authProvider.notifier).setUnauthenticated();
      }
    }
    // GoRouter réagit automatiquement au changement d'état de authProvider
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;

    return Scaffold(
      backgroundColor: AppColors.primary,
      body: SafeArea(
        child: Center(
          child: FadeTransition(
            opacity: _fadeAnim,
            child: ScaleTransition(
              scale: _scaleAnim,
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Container(
                    width: size.width * 0.28,
                    height: size.width * 0.28,
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.15),
                      shape: BoxShape.circle,
                    ),
                    child: const Icon(
                      Icons.home_work_rounded,
                      size: 60,
                      color: Colors.white,
                    ),
                  ),
                  const SizedBox(height: 24),
                  Text(
                    'ImmoGo',
                    style: Theme.of(context).textTheme.displayLarge?.copyWith(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: size.width * 0.09,
                        ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Votre agence immobilière au Bénin',
                    style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                          color: Colors.white70,
                          fontSize: size.width * 0.035,
                        ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 64),
                  const SizedBox(
                    width: 28,
                    height: 28,
                    child: CircularProgressIndicator(
                      color: Colors.white,
                      strokeWidth: 2.5,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
