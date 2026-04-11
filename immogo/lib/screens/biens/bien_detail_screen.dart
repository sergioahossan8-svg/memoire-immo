// lib/screens/biens/bien_detail_screen.dart
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/theme/app_theme.dart';
import '../../core/utils/formatters.dart';
import '../../models/bien_model.dart';
import '../../providers/auth_provider.dart';
import '../../providers/bien_provider.dart';
import '../../providers/favori_provider.dart';
import '../../widgets/bien/bien_card.dart';
import '../../widgets/common/custom_button.dart';
import '../../widgets/common/loading_widget.dart';

class BienDetailScreen extends ConsumerWidget {
  final int id;

  const BienDetailScreen({super.key, required this.id});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final detailAsync = ref.watch(bienDetailProvider(id));
    final isAuth = ref.watch(authProvider).status == AuthStatus.authenticated;

    return Scaffold(
      body: detailAsync.when(
        loading: () => const LoadingWidget(message: 'Chargement...'),
        error: (e, _) => AppErrorWidget(
          message: 'Impossible de charger ce bien.',
          onRetry: () => ref.refresh(bienDetailProvider(id)),
        ),
        data: (data) {
          final bien = data['bien'] as BienModel;
          final similaires = data['similaires'] as List<BienModel>;
          final isFavori = isAuth
              ? ref.read(favoriProvider.notifier).isFavori(bien.id)
              : false;
          final photos = bien.photos ?? [];
          final allPhotos = photos.isNotEmpty
              ? photos.map((p) => p.url).toList()
              : (bien.photo != null ? [bien.photo!] : <String>[]);

          return CustomScrollView(
            slivers: [
              // App bar avec photos
              SliverAppBar(
                expandedHeight: 280,
                pinned: true,
                flexibleSpace: FlexibleSpaceBar(
                  background: allPhotos.isNotEmpty
                      ? PageView.builder(
                          itemCount: allPhotos.length,
                          itemBuilder: (_, i) => CachedNetworkImage(
                            imageUrl: allPhotos[i],
                            fit: BoxFit.cover,
                            errorWidget: (_, __, ___) => Container(
                              color: Colors.grey[200],
                              child: const Icon(Icons.home_work_rounded,
                                  size: 80, color: Colors.grey),
                            ),
                          ),
                        )
                      : Container(
                          color: Colors.grey[200],
                          child: const Icon(Icons.home_work_rounded,
                              size: 80, color: Colors.grey),
                        ),
                ),
                actions: [
                  if (isAuth)
                    IconButton(
                      icon: Icon(
                          isFavori ? Icons.favorite : Icons.favorite_border,
                          color: isFavori ? Colors.red : Colors.white),
                      onPressed: () =>
                          ref.read(favoriProvider.notifier).toggle(bien.id),
                    ),
                ],
              ),
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Badge premium + transaction
                      Row(children: [
                        if (bien.isPremium) ...[
                          Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 10, vertical: 4),
                            decoration: BoxDecoration(
                              color: AppColors.premium,
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: const Text('PREMIUM',
                                style: TextStyle(
                                    color: Colors.white,
                                    fontSize: 11,
                                    fontWeight: FontWeight.bold)),
                          ),
                          const SizedBox(width: 8),
                        ],
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: bien.transaction == 'vente'
                                ? AppColors.primary
                                : AppColors.secondary,
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            bien.transaction.toUpperCase(),
                            style: const TextStyle(
                                color: Colors.white,
                                fontSize: 11,
                                fontWeight: FontWeight.bold),
                          ),
                        ),
                        const Spacer(),
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: AppColors.success.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: AppColors.success),
                          ),
                          child: Text(
                            Formatters.statut(bien.statut),
                            style: const TextStyle(
                                color: AppColors.success,
                                fontSize: 11,
                                fontWeight: FontWeight.w600),
                          ),
                        ),
                      ]),
                      const SizedBox(height: 12),
                      Text(bien.titre,
                          style: Theme.of(context).textTheme.headlineMedium),
                      const SizedBox(height: 8),
                      Row(children: [
                        const Icon(Icons.location_on,
                            size: 16, color: AppColors.textSecondary),
                        const SizedBox(width: 4),
                        Expanded(
                          child: Text(
                            '${bien.localisation}, ${bien.ville}',
                            style: Theme.of(context).textTheme.bodyLarge,
                          ),
                        ),
                      ]),
                      const SizedBox(height: 16),
                      // Prix
                      Text(
                        bien.prixFormate.isNotEmpty
                            ? bien.prixFormate
                            : Formatters.prix(bien.prix),
                        style: Theme.of(context)
                            .textTheme
                            .headlineMedium
                            ?.copyWith(
                                color: AppColors.accent,
                                fontWeight: FontWeight.bold),
                      ),
                      const SizedBox(height: 20),
                      // Caractéristiques
                      _caracteristiquesRow(context, bien),
                      const SizedBox(height: 20),
                      const Divider(),
                      const SizedBox(height: 16),
                      // Description
                      if (bien.description != null &&
                          bien.description!.isNotEmpty) ...[
                        Text('Description',
                            style: Theme.of(context).textTheme.titleLarge),
                        const SizedBox(height: 8),
                        Text(bien.description!,
                            style: Theme.of(context).textTheme.bodyLarge),
                        const SizedBox(height: 20),
                        const Divider(),
                        const SizedBox(height: 16),
                      ],
                      // Agence
                      if (bien.agenceDetail != null) ...[
                        Text('Agence',
                            style: Theme.of(context).textTheme.titleLarge),
                        const SizedBox(height: 12),
                        _agenceCard(context, bien.agenceDetail!),
                        const SizedBox(height: 20),
                        const Divider(),
                        const SizedBox(height: 16),
                      ],
                      // Boutons action
                      if (isAuth && bien.statut == 'disponible') ...[
                        Text('Passer à l\'action',
                            style: Theme.of(context).textTheme.titleLarge),
                        const SizedBox(height: 12),
                        CustomButton(
                          label: 'Réserver (acompte 10%)',
                          icon: Icons.handshake_outlined,
                          onPressed: () =>
                              context.push('/reservation/${bien.id}'),
                        ),
                        const SizedBox(height: 12),
                        CustomButton(
                          label: 'Payer en totalité',
                          icon: Icons.payment,
                          backgroundColor: AppColors.success,
                          onPressed: () =>
                              _payerComplet(context, ref, bien),
                        ),
                        const SizedBox(height: 20),
                        const Divider(),
                        const SizedBox(height: 16),
                      ] else if (!isAuth && bien.statut == 'disponible') ...[
                        OutlinedButton.icon(
                          onPressed: () => context.go('/login'),
                          icon: const Icon(Icons.login),
                          label: const Text('Connectez-vous pour réserver'),
                        ),
                        const SizedBox(height: 20),
                        const Divider(),
                        const SizedBox(height: 16),
                      ],
                      // Biens similaires
                      if (similaires.isNotEmpty) ...[
                        Text('Biens similaires',
                            style: Theme.of(context).textTheme.titleLarge),
                        const SizedBox(height: 12),
                        SizedBox(
                          height: 300,
                          child: ListView.builder(
                            scrollDirection: Axis.horizontal,
                            itemCount: similaires.length,
                            itemBuilder: (_, i) => SizedBox(
                              width: 260,
                              child: Padding(
                                padding: const EdgeInsets.only(right: 12),
                                child: BienCard(
                                  bien: similaires[i],
                                  showFavoriButton: false,
                                  onTap: () => context
                                      .push('/biens/${similaires[i].id}'),
                                ),
                              ),
                            ),
                          ),
                        ),
                      ],
                      const SizedBox(height: 32),
                    ],
                  ),
                ),
              ),
            ],
          );
        },
      ),
    );
  }

  Widget _caracteristiquesRow(BuildContext context, BienModel bien) {
    final items = <Widget>[];
    if (bien.superficie != null)
      items.add(_caracteristique(
          context, Icons.square_foot, Formatters.superficie(bien.superficie)));
    if (bien.chambres != null)
      items.add(_caracteristique(
          context, Icons.bed, '${bien.chambres} chambre(s)'));
    if (bien.sallesBain != null)
      items.add(_caracteristique(
          context, Icons.bathroom_outlined, '${bien.sallesBain} SDB'));
    if (bien.typeBien != null)
      items.add(
          _caracteristique(context, Icons.category_outlined, bien.typeBien!));

    if (items.isEmpty) return const SizedBox.shrink();
    return Wrap(spacing: 12, runSpacing: 12, children: items);
  }

  Widget _caracteristique(
      BuildContext context, IconData icon, String label) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: AppColors.primary.withOpacity(0.07),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Row(mainAxisSize: MainAxisSize.min, children: [
        Icon(icon, size: 16, color: AppColors.primary),
        const SizedBox(width: 6),
        Text(label,
            style: const TextStyle(
                fontSize: 13,
                fontWeight: FontWeight.w500,
                color: AppColors.textPrimary)),
      ]),
    );
  }

  Widget _agenceCard(BuildContext context, AgenceDetailModel agence) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.divider),
      ),
      child: Row(
        children: [
          if (agence.logo != null)
            ClipRRect(
              borderRadius: BorderRadius.circular(8),
              child: CachedNetworkImage(
                imageUrl: agence.logo!,
                width: 50,
                height: 50,
                fit: BoxFit.cover,
                errorWidget: (_, __, ___) =>
                    const Icon(Icons.business, size: 40, color: Colors.grey),
              ),
            )
          else
            const Icon(Icons.business, size: 40, color: AppColors.primary),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(agence.nom,
                    style: Theme.of(context).textTheme.titleMedium),
                if (agence.ville != null)
                  Text(agence.ville!,
                      style: Theme.of(context).textTheme.bodyMedium),
                if (agence.secteur != null)
                  Text(agence.secteur!,
                      style: Theme.of(context).textTheme.labelSmall),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Future<void> _payerComplet(
      BuildContext context, WidgetRef ref, BienModel bien) async {
    // Demander le type de contrat avant la navigation
    final typeContrat = await showDialog<String>(
      context: context,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text('Type de contrat'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            ListTile(
              leading: const Icon(Icons.key_outlined, color: AppColors.secondary),
              title: const Text('Location'),
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8)),
              onTap: () => Navigator.pop(context, 'location'),
            ),
            ListTile(
              leading: const Icon(Icons.home_work_outlined, color: AppColors.primary),
              title: const Text('Vente'),
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(8)),
              onTap: () => Navigator.pop(context, 'vente'),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('Annuler'),
          ),
        ],
      ),
    );
    if (typeContrat == null || !context.mounted) return;

    // Rediriger vers l'écran réservation avec le paramètre type=complet
    context.push('/reservation/${bien.id}?type=complet&type_contrat=$typeContrat');
  }
}
