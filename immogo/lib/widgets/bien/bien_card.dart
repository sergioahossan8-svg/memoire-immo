// lib/widgets/bien/bien_card.dart
import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import '../../core/theme/app_theme.dart';
import '../../models/bien_model.dart';
import '../../core/utils/formatters.dart';

class BienCard extends StatelessWidget {
  final BienModel bien;
  final VoidCallback onTap;
  final VoidCallback? onFavoriTap;
  final bool isFavori;
  final bool showFavoriButton;

  const BienCard({
    super.key,
    required this.bien,
    required this.onTap,
    this.onFavoriTap,
    this.isFavori = false,
    this.showFavoriButton = true,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Card(
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image
            Stack(
              children: [
                ClipRRect(
                  borderRadius:
                      const BorderRadius.vertical(top: Radius.circular(16)),
                  child: bien.photo != null
                      ? CachedNetworkImage(
                          imageUrl: bien.photo!,
                          height: 180,
                          width: double.infinity,
                          fit: BoxFit.cover,
                          placeholder: (_, __) => Container(
                            height: 180,
                            color: Colors.grey[200],
                            child: const Center(
                                child: CircularProgressIndicator()),
                          ),
                          errorWidget: (_, __, ___) => _noPhoto(),
                        )
                      : _noPhoto(),
                ),
                // Badge premium
                if (bien.isPremium)
                  Positioned(
                    top: 10,
                    left: 10,
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 8, vertical: 4),
                      decoration: BoxDecoration(
                        color: AppColors.premium,
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: const Text('PREMIUM',
                          style: TextStyle(
                              color: Colors.white,
                              fontSize: 10,
                              fontWeight: FontWeight.bold)),
                    ),
                  ),
                // Bouton favori
                if (showFavoriButton)
                  Positioned(
                    top: 8,
                    right: 8,
                    child: Container(
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.9),
                        shape: BoxShape.circle,
                      ),
                      child: IconButton(
                        icon: Icon(
                            isFavori
                                ? Icons.favorite
                                : Icons.favorite_border,
                            color: isFavori ? Colors.red : Colors.grey),
                        onPressed: onFavoriTap,
                        iconSize: 20,
                        constraints: const BoxConstraints(),
                        padding: const EdgeInsets.all(8),
                      ),
                    ),
                  ),
                // Badge transaction
                Positioned(
                  bottom: 8,
                  right: 8,
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 8, vertical: 4),
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
                          fontSize: 10,
                          fontWeight: FontWeight.bold),
                    ),
                  ),
                ),
              ],
            ),
            // Infos
            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    bien.titre,
                    style: Theme.of(context).textTheme.titleMedium,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 4),
                  Row(children: [
                    const Icon(Icons.location_on,
                        size: 14, color: AppColors.textSecondary),
                    const SizedBox(width: 2),
                    Expanded(
                      child: Text(
                        '${bien.localisation}, ${bien.ville}',
                        style: Theme.of(context).textTheme.bodyMedium,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ]),
                  const SizedBox(height: 8),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        bien.prixFormate.isNotEmpty
                            ? bien.prixFormate
                            : Formatters.prix(bien.prix),
                        style: Theme.of(context).textTheme.titleMedium?.copyWith(
                              color: AppColors.accent,
                              fontWeight: FontWeight.bold,
                            ),
                      ),
                      Row(children: [
                        if (bien.chambres != null) ...[
                          const Icon(Icons.bed,
                              size: 14, color: AppColors.textSecondary),
                          Text(' ${bien.chambres}',
                              style:
                                  Theme.of(context).textTheme.bodyMedium),
                          const SizedBox(width: 8),
                        ],
                        if (bien.superficie != null) ...[
                          const Icon(Icons.square_foot,
                              size: 14, color: AppColors.textSecondary),
                          Text(
                              ' ${bien.superficie!.toStringAsFixed(0)}m²',
                              style:
                                  Theme.of(context).textTheme.bodyMedium),
                        ],
                      ]),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _noPhoto() => Container(
        height: 180,
        color: Colors.grey[200],
        child:
            const Icon(Icons.home_work_rounded, size: 60, color: Colors.grey),
      );
}
