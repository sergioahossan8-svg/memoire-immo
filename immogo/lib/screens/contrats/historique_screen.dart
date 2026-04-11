// lib/screens/contrats/historique_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/theme/app_theme.dart';
import '../../providers/auth_provider.dart';
import '../../providers/contrat_provider.dart';
import '../../widgets/contrat/contrat_card.dart';
import '../../widgets/common/loading_widget.dart';

class HistoriqueScreen extends ConsumerWidget {
  const HistoriqueScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final isAuth = ref.watch(authProvider).status == AuthStatus.authenticated;

    if (!isAuth) {
      return Scaffold(
        appBar: AppBar(title: const Text('Mes contrats')),
        body: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.receipt_long_outlined,
                  size: 64, color: AppColors.textSecondary),
              const SizedBox(height: 16),
              Text('Connectez-vous pour voir vos contrats',
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

    // Charger à l'affichage
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (ref.read(contratListProvider) is! AsyncData) {
        ref.read(contratListProvider.notifier).load();
      }
    });

    final contratsAsync = ref.watch(contratListProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Mes contrats')),
      body: contratsAsync.when(
        loading: () => const LoadingWidget(message: 'Chargement...'),
        error: (e, _) => AppErrorWidget(
          message: 'Erreur de chargement.',
          onRetry: () => ref.read(contratListProvider.notifier).load(),
        ),
        data: (contrats) {
          if (contrats.isEmpty) {
            return const EmptyWidget(
              message:
                  'Vous n\'avez pas encore de contrats.\nRéservez un bien pour commencer.',
              icon: Icons.receipt_long_outlined,
            );
          }
          return RefreshIndicator(
            onRefresh: () => ref.read(contratListProvider.notifier).load(),
            child: ListView.builder(
              padding: const EdgeInsets.all(16),
              itemCount: contrats.length,
              itemBuilder: (_, i) => Padding(
                padding: const EdgeInsets.only(bottom: 16),
                child: ContratCard(
                  contrat: contrats[i],
                  onTap: () =>
                      context.push('/contrats/${contrats[i].id}'),
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}
