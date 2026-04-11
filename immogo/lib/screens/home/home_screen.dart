// lib/screens/home/home_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../biens/biens_list_screen.dart';
import '../favoris/favoris_screen.dart';
import '../contrats/historique_screen.dart';
import '../profil/profil_screen.dart';
import '../notifications/notifications_screen.dart';
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

  final List<Widget> _pages = const [
    BiensListScreen(),
    FavorisScreen(),
    HistoriqueScreen(),
    ProfilScreen(),
  ];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final isAuth =
          ref.read(authProvider).status == AuthStatus.authenticated;
      if (isAuth) {
        // Charger les notifications en arrière-plan sans afficher d'écran
        ref.read(notificationProvider.notifier).load();
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final unreadCount = ref.watch(notificationProvider).unreadCount;
    final isAuth =
        ref.watch(authProvider).status == AuthStatus.authenticated;

    return Scaffold(
      body: _pages[_currentIndex],
      bottomNavigationBar: BottomNavigationBar(
        currentIndex: _currentIndex,
        onTap: (i) => setState(() => _currentIndex = i),
        items: [
          const BottomNavigationBarItem(
              icon: Icon(Icons.search), label: 'Biens'),
          BottomNavigationBarItem(
            icon: Icon(isAuth ? Icons.favorite : Icons.favorite_border),
            label: 'Favoris',
          ),
          const BottomNavigationBarItem(
              icon: Icon(Icons.receipt_long_outlined), label: 'Historique'),
          const BottomNavigationBarItem(
              icon: Icon(Icons.person_outline), label: 'Profil'),
        ],
      ),
    );
  }
}
