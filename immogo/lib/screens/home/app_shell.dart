// lib/screens/home/app_shell.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../providers/auth_provider.dart';
import '../../providers/notification_provider.dart';
import '../../core/theme/app_theme.dart';

class AppShell extends ConsumerWidget {
  final Widget child;
  const AppShell({super.key, required this.child});

  int _currentIndex(String loc, bool isAuth) {
    if (!isAuth) return 0;
    if (loc == '/rechercher') return 1;
    if (loc.startsWith('/favoris')) return 2;
    if (loc.startsWith('/historique') || loc.startsWith('/contrats')) return 3;
    if (loc.startsWith('/profil')) return 4;
    return 0;
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final authState  = ref.watch(authProvider);
    final isAuth     = authState.status == AuthStatus.authenticated;
    final loc        = GoRouterState.of(context).matchedLocation;
    final currentIdx = _currentIndex(loc, isAuth);
    final unread     = isAuth
        ? ref.watch(notificationProvider).unreadCount
        : 0;

    return Scaffold(
      body: child,
      bottomNavigationBar: isAuth
          ? _buildAuthNav(context, currentIdx, unread)
          : _buildGuestNav(context),
    );
  }

  Widget _buildAuthNav(BuildContext context, int currentIndex, int unread) {
    return BottomNavigationBar(
      currentIndex: currentIndex,
      onTap: (i) {
        switch (i) {
          case 0: context.go('/'); break;
          case 1: context.go('/rechercher'); break;
          case 2: context.go('/favoris'); break;
          case 3: context.go('/historique'); break;
          case 4: context.go('/profil'); break;
        }
      },
      type: BottomNavigationBarType.fixed,
      selectedItemColor: AppColors.primary,
      unselectedItemColor: AppColors.textSecondary,
      selectedLabelStyle: const TextStyle(
          fontSize: 11, fontWeight: FontWeight.w600),
      unselectedLabelStyle: const TextStyle(fontSize: 11),
      items: [
        const BottomNavigationBarItem(
          icon: Icon(Icons.home_outlined),
          activeIcon: Icon(Icons.home_rounded),
          label: 'Accueil',
        ),
        const BottomNavigationBarItem(
          icon: Icon(Icons.search_outlined),
          activeIcon: Icon(Icons.search_rounded),
          label: 'Rechercher',
        ),
        const BottomNavigationBarItem(
          icon: Icon(Icons.favorite_border),
          activeIcon: Icon(Icons.favorite),
          label: 'Favoris',
        ),
        BottomNavigationBarItem(
          icon: unread > 0
              ? Badge(
                  label: Text('$unread'),
                  child: const Icon(Icons.receipt_long_outlined),
                )
              : const Icon(Icons.receipt_long_outlined),
          activeIcon: const Icon(Icons.receipt_long_rounded),
          label: 'Historique',
        ),
        const BottomNavigationBarItem(
          icon: Icon(Icons.person_outline),
          activeIcon: Icon(Icons.person_rounded),
          label: 'Profil',
        ),
      ],
    );
  }

  Widget _buildGuestNav(BuildContext context) {
    return BottomNavigationBar(
      currentIndex: 0,
      onTap: (i) {
        if (i == 1) context.go('/login');
      },
      type: BottomNavigationBarType.fixed,
      selectedItemColor: AppColors.primary,
      unselectedItemColor: AppColors.textSecondary,
      selectedLabelStyle: const TextStyle(
          fontSize: 11, fontWeight: FontWeight.w600),
      unselectedLabelStyle: const TextStyle(fontSize: 11),
      items: const [
        BottomNavigationBarItem(
          icon: Icon(Icons.home_outlined),
          activeIcon: Icon(Icons.home_rounded),
          label: 'Accueil',
        ),
        BottomNavigationBarItem(
          icon: Icon(Icons.login_rounded),
          label: 'Connexion',
        ),
      ],
    );
  }
}
