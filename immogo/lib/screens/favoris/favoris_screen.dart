// lib/screens/favoris/favoris_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/theme/app_theme.dart';
import '../../providers/auth_provider.dart';
import '../../providers/favori_provider.dart';
import '../../widgets/bien/bien_card.dart';
import '../../widgets/common/loading_widget.dart';

class FavorisScreen extends ConsumerWidget {
  const FavorisScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final isAuth = ref.watch(authProvider).status == AuthStatus.authenticated;

    if (!isAuth) {
      return Scaffold(
        appBar: AppBar(title: const Text('Mes favoris')),
        body: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.favorite_border,
                  size: 64, color: AppColors.textSecondary),
              const SizedBox(height: 16),
              Text('Connectez-vous pour voir vos favoris',
                  style: Theme.of(context).textTheme.bodyLarge),
              const SizedBox(height: 16),
              ElevatedButton(
                  onPressed: () => context.go('/login'),
                  child: const Text('Se connecter')),
            ],
          ),
        ),
      );
    }

    final favorisAsync = ref.watch(favoriProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Mes favoris')),
      body: favorisAsync.when(
        loading: () => const LoadingWidget(message: 'Chargement...'),
        error: (e, _) => AppErrorWidget(
          message: 'Erreur de chargement.',
          onRetry: () => ref.read(favoriProvider.notifier).load(),
        ),
        data: (favoris) {
          if (favoris.isEmpty) {
            return const EmptyWidget(
              message: 'Vous n\'avez pas encore\nde biens favoris.',
              icon: Icons.favorite_border,
            );
          }
          return RefreshIndicator(
            onRefresh: () => ref.read(favoriProvider.notifier).load(),
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: favoris.length,
              itemBuilder: (_, i) {
                final bien = favoris[i];
                return Padding(
                  padding: const EdgeInsets.only(bottom: 16),
                  child: BienCard(
                    bien: bien,
                    isFavori: true,
                    onTap: () => context.push('/biens/${bien.id}'),
                    onFavoriTap: () =>
                        ref.read(favoriProvider.notifier).toggle(bien.id),
                  ),
                );
              },
            ),
          );
        },
      ),
    );
  }
}
