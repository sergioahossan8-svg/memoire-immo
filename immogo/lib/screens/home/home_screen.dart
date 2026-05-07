// lib/screens/home/home_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../biens/biens_list_screen.dart';
import '../favoris/favoris_screen.dart';
import '../contrats/historique_screen.dart';
import '../profil/profil_screen.dart';
import '../../providers/notification_provider.dart';
import '../../providers/auth_provider.dart';
import '../../core/theme/app_theme.dart';

class HomeScreen extends ConsumerStatefulWidget {
  const HomeScreen({super.key});

  @override
  ConsumerState<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends ConsumerState<HomeScreen> {
  int _currentIndex = 0;

  // Pages disponibles pour les utilisateurs connectés
  static const List<Widget> _authPages = [
    BiensListScreen(),
    FavorisScreen(),
    HistoriqueScreen(),
    ProfilScreen(),
  ];

  // Page unique pour les non-connectés
  static const List<Widget> _guestPages = [
    BiensListScreen(),
  ];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final isAuth =
          ref.read(authProvider).status == AuthStatus.authenticated;
      if (isAuth) {
        ref.read(notificationProvider.notifier).load();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final unreadCount = ref.watch(notificationProvider).unreadCount;
    final isAuth =
        ref.watch(authProvider).status == AuthStatus.authenticated;

    // Si l'état change (connexion/déconnexion), revenir à l'index 0
    final pages = isAuth ? _authPages : _guestPages;
    final safeIndex = _currentIndex < pages.length ? _currentIndex : 0;

    return Scaffold(
      body: pages[safeIndex],
      bottomNavigationBar: isAuth
          ? _buildAuthNav(safeIndex, unreadCount)
          : _buildGuestNav(),
    );
  }

  // Barre de navigation pour utilisateur connecté
  Widget _buildAuthNav(int currentIndex, int unreadCount) {
    return BottomNavigationBar(
      currentIndex: currentIndex,
      onTap: (i) => setState(() => _currentIndex = i),
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
          icon: unreadCount > 0
              ? Badge(
                  label: Text('$unreadCount'),
                  child: const Icon(Icons.person_outline),
                )
              : const Icon(Icons.person_outline),
          activeIcon: const Icon(Icons.person),
          label: 'Profil',
        ),
      ],
    );
  }

  // Barre de navigation pour visiteur non connecté
  Widget _buildGuestNav() {
    return BottomNavigationBar(
      currentIndex: 0,
      onTap: (i) {
        if (i == 1) {
          // Bouton Connexion → aller sur la page login
          context.go('/login');
        }
      },
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
