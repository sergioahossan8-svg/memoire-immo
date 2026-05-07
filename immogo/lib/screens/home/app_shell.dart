// lib/screens/home/app_shell.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../providers/auth_provider.dart';
import '../../core/theme/app_theme.dart';

class AppShell extends ConsumerStatefulWidget {
  final Widget child;
  const AppShell({super.key, required this.child});

  @override
  ConsumerState<AppShell> createState() => _AppShellState();
}

class _AppShellState extends ConsumerState<AppShell> {
  @override
  void initState() {
    super.initState();
  }

  // Retourne l'index actif selon la route courante
  int _currentIndex(BuildContext context) {
    final loc = GoRouterState.of(context).matchedLocation;
    if (loc.startsWith('/favoris'))   return 1;
    if (loc.startsWith('/historique') || loc.startsWith('/contrats')) return 2;
    if (loc.startsWith('/profil'))    return 3;
    return 0; // Biens (défaut)
  }

  void _onTap(BuildContext context, int index, bool isAuth) {
    if (!isAuth) {
      // Non connecté : seulement Biens (0) et Connexion (1)
      if (index == 1) context.go('/login');
      return;
    }
    switch (index) {
      case 0: context.go('/');          break;
      case 1: context.go('/favoris');   break;
      case 2: context.go('/historique'); break;
      case 3: context.go('/profil');    break;
    }
  }

  @override
  Widget build(BuildContext context) {
    final isAuth = ref.watch(authProvider).status == AuthStatus.authenticated;
    final currentIdx = _currentIndex(context);

    return Scaffold(
      body: widget.child,
      bottomNavigationBar: isAuth
          ? _buildAuthNav(context, currentIdx)
          : _buildGuestNav(context, currentIdx),
    );
  }

  Widget _buildAuthNav(BuildContext context, int currentIndex) {
    return BottomNavigationBar(
      currentIndex: currentIndex,
      onTap: (i) => _onTap(context, i, true),
      type: BottomNavigationBarType.fixed,
      selectedItemColor: AppColors.primary,
      unselectedItemColor: AppColors.textSecondary,
      items: [
        const BottomNavigationBarItem(
          icon: Icon(Icons.search),
          label: 'Biens',
        ),
        const BottomNavigationBarItem(
          icon: Icon(Icons.favorite_border),
          activeIcon: Icon(Icons.favorite),
          label: 'Favoris',
        ),
        const BottomNavigationBarItem(
          icon: Icon(Icons.receipt_long_outlined),
          label: 'Historique',
        ),
        BottomNavigationBarItem(
          icon: const Icon(Icons.person_outline),
          activeIcon: const Icon(Icons.person),
          label: 'Profil',
        ),
      ],
    );
  }

  Widget _buildGuestNav(BuildContext context, int currentIndex) {
    return BottomNavigationBar(
      currentIndex: 0,
      onTap: (i) => _onTap(context, i, false),
      type: BottomNavigationBarType.fixed,
      selectedItemColor: AppColors.primary,
      unselectedItemColor: AppColors.textSecondary,
      items: const [
        BottomNavigationBarItem(
          icon: Icon(Icons.search),
          label: 'Biens',
        ),
        BottomNavigationBarItem(
          icon: Icon(Icons.login_rounded),
          label: 'Connexion',
        ),
      ],
    );
  }
}
