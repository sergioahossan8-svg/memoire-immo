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

class _SplashScreenState extends ConsumerState<SplashScreen>
    with SingleTickerProviderStateMixin {
  bool _hasNavigated = false;
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
    if (_hasNavigated) return;

    // Laisser l'animation démarrer (minimum 600ms pour ne pas flasher)
    final minDelay = Future.delayed(const Duration(milliseconds: 800));

    // Vérifier d'abord le token local (instantané)
    final authService = ref.read(authServiceProvider);
    final hasToken = await authService.hasLocalToken();

    if (!hasToken) {
      // Pas de token → login direct sans appel réseau
      await minDelay;
      _navigate('/login');
      return;
    }

    // Token présent → valider côté serveur avec timeout max 25s
    bool timedOut = false;
    try {
      await Future.wait([
        minDelay,
        ref.read(authProvider.notifier).checkAuth().timeout(
          const Duration(seconds: 25),
          onTimeout: () {
            timedOut = true;
            // Timeout réseau → mode optimiste : on fait confiance au token local
          },
        ),
      ]);
    } catch (_) {
      timedOut = true;
    }

    if (!mounted || _hasNavigated) return;

    if (timedOut) {
      // Pas pu joindre le serveur mais token local présent → on laisse entrer
      // L'app se déconnectera si le serveur répond 401 plus tard
      _navigate('/');
      return;
    }

    final status = ref.read(authProvider).status;
    _navigate(status == AuthStatus.authenticated ? '/' : '/login');
  }

  void _navigate(String path) {
    if (_hasNavigated || !mounted) return;
    _hasNavigated = true;
    context.go(path);
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
