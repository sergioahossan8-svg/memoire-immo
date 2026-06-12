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
    final authState = ref.watch(authProvider);
    final isAuth = authState.status == AuthStatus.authenticated;
    // Admins et super-admins ne peuvent pas ajouter en favoris
    final userRole = authState.user?.role ?? '';
    final isClient = userRole == 'client' || userRole == '';
    final canFavori = isAuth && isClient;

    // Charger les favoris si client auth et pas encore chargés
    if (canFavori) {
      final favorisState = ref.watch(favoriProvider);
      if (favorisState is AsyncLoading) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          ref.read(favoriProvider.notifier).load();
        });
      }
    }

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
          // watch pour être réactif aux changements de favoris (clients seulement)
          final favorisState = canFavori ? ref.watch(favoriProvider) : null;
          final isFavori = canFavori
              ? (favorisState?.valueOrNull?.any((b) => b.id == bien.id) ?? false)
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
                  // Bouton favori toujours visible — redirige vers login si non connecté
                  IconButton(
                    icon: Icon(
                      isFavori ? Icons.favorite : Icons.favorite_border,
                      color: isFavori ? Colors.red : Colors.white,
                    ),
                    onPressed: () {
                      if (!isAuth) {
                        showDialog(
                          context: context,
                          useRootNavigator: true,
                          builder: (ctx) => AlertDialog(
                            title: const Text('Connexion requise'),
                            content: const Text(
                                'Connectez-vous pour ajouter ce bien à vos favoris.'),
                            actions: [
                              TextButton(
                                onPressed: () => Navigator.of(ctx, rootNavigator: true).pop(),
                                child: const Text('Annuler'),
                              ),
                              ElevatedButton(
                                onPressed: () {
                                  Navigator.of(ctx, rootNavigator: true).pop();
                                  context.go('/login');
                                },
                                child: const Text('Se connecter'),
                              ),
                            ],
                          ),
                        );
                        return;
                      }
                      if (canFavori) {
                        ref.read(favoriProvider.notifier).toggle(bien.id);
                      }
                    },
                  ),
                ],
              ),
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.all(20),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Badge transaction
                      Row(children: [
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
                            color: _statutColor(bien.statut).withOpacity(0.1),
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(color: _statutColor(bien.statut)),
                          ),
                          child: Text(
                            Formatters.statut(bien.statut),
                            style: TextStyle(
                                color: _statutColor(bien.statut),
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
                      // Conditions de location
                      if (bien.transaction == 'location' &&
                          bien.avanceMois != null) ...[
                        const SizedBox(height: 20),
                        _conditionsLocationCard(context, bien),
                      ],
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
                      // Boutons action (seulement si disponible et client)
                      if (isClient && bien.statut == 'disponible') ...[
                        Text('Passer à l\'action',
                            style: Theme.of(context).textTheme.titleLarge),
                        const SizedBox(height: 12),
                        CustomButton(
                          label: bien.transaction == 'location'
                              ? 'Réserver (10% de ${Formatters.prix(bien.montantTotal)})'
                              : 'Réserver (acompte 10%)',
                          icon: Icons.handshake_outlined,
                          onPressed: () =>
                              context.push('/reservation/${bien.id}'),
                        ),
                        const SizedBox(height: 12),
                        CustomButton(
                          label: bien.transaction == 'location'
                              ? 'Payer en totalité (${Formatters.prix(bien.montantTotal)})'
                              : 'Payer en totalité',
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
                      ] else if (bien.statut != 'disponible') ...[
                        // Afficher message informatif si le bien n'est plus disponible
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: _statutColor(bien.statut).withOpacity(0.08),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(
                                color: _statutColor(bien.statut).withOpacity(0.3)),
                          ),
                          child: Row(
                            children: [
                              Icon(_statutIcon(bien.statut),
                                  color: _statutColor(bien.statut), size: 22),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Text(
                                  _statutMessage(bien.statut),
                                  style: Theme.of(context)
                                      .textTheme
                                      .bodyMedium
                                      ?.copyWith(color: _statutColor(bien.statut)),
                                ),
                              ),
                            ],
                          ),
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

  Widget _conditionsLocationCard(BuildContext context, BienModel bien) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.primary.withOpacity(0.05),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: AppColors.primary.withOpacity(0.25)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.receipt_long_outlined,
                  size: 16, color: AppColors.primary),
              const SizedBox(width: 6),
              Text(
                'Conditions de location',
                style: Theme.of(context).textTheme.titleSmall?.copyWith(
                    color: AppColors.primary, fontWeight: FontWeight.bold),
              ),
            ],
          ),
          const SizedBox(height: 12),
          _conditionRow(
            context,
            Icons.calendar_today_outlined,
            'Avance',
            '${bien.avanceMois ?? 1} mois (${Formatters.prix(bien.prix * (bien.avanceMois ?? 1))})',
          ),
          if ((bien.cautionEau ?? 0) > 0) ...[
            const SizedBox(height: 8),
            _conditionRow(
              context,
              Icons.water_drop_outlined,
              'Caution eau',
              Formatters.prix(bien.cautionEau!),
            ),
          ],
          if ((bien.cautionElectricite ?? 0) > 0) ...[
            const SizedBox(height: 8),
            _conditionRow(
              context,
              Icons.bolt_outlined,
              'Caution électricité',
              Formatters.prix(bien.cautionElectricite!),
            ),
          ],
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 10),
            child: Divider(height: 1),
          ),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(children: [
                const Icon(Icons.calculate_outlined,
                    size: 16, color: AppColors.accent),
                const SizedBox(width: 6),
                Text('Total à régler',
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                        fontWeight: FontWeight.w600)),
              ]),
              Text(
                Formatters.prix(bien.montantTotal),
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    color: AppColors.accent, fontWeight: FontWeight.bold),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _conditionRow(
      BuildContext context, IconData icon, String label, String value) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Row(children: [
          Icon(icon, size: 15, color: AppColors.textSecondary),
          const SizedBox(width: 6),
          Text(label,
              style: Theme.of(context)
                  .textTheme
                  .bodyMedium
                  ?.copyWith(color: AppColors.textSecondary)),
        ]),
        Text(value,
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                fontWeight: FontWeight.w600, color: AppColors.textPrimary)),
      ],
    );
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
    // Le type de contrat est celui du bien (defini par l'admin, non modifiable)
    // Le montant tient compte des conditions de location (avance + cautions)
    context.push('/reservation/${bien.id}?type=complet');
  }

  Color _statutColor(String statut) {
    switch (statut) {
      case 'disponible':
        return AppColors.success;
      case 'reserve':
        return AppColors.premium;
      case 'vendu':
        return AppColors.error;
      case 'loue':
        return AppColors.secondary;
      default:
        return AppColors.textSecondary;
    }
  }

  IconData _statutIcon(String statut) {
    switch (statut) {
      case 'reserve':
        return Icons.hourglass_empty;
      case 'vendu':
        return Icons.sell;
      case 'loue':
        return Icons.key;
      default:
        return Icons.block;
    }
  }

  String _statutMessage(String statut) {
    switch (statut) {
      case 'reserve':
        return 'Ce bien est actuellement réservé et en attente de paiement du solde.';
      case 'vendu':
        return 'Ce bien a été vendu et n\'est plus disponible.';
      case 'loue':
        return 'Ce bien est actuellement loué.';
      default:
        return 'Ce bien n\'est pas disponible à la vente ou location pour le moment.';
    }
  }
}
