// lib/widgets/contrat/contrat_card.dart
import 'package:flutter/material.dart';
import '../../core/theme/app_theme.dart';
import '../../core/utils/formatters.dart';
import '../../models/contrat_model.dart';

class ContratCard extends StatelessWidget {
  final ContratModel contrat;
  final VoidCallback onTap;

  const ContratCard({super.key, required this.contrat, required this.onTap});

  Color _statutColor(String s) {
    switch (s) {
      case 'actif':       return AppColors.success;
      case 'en_attente':  return AppColors.premium;
      case 'annule':      return AppColors.error;
      default:            return AppColors.textSecondary;
    }
  }

  @override
  Widget build(BuildContext context) {
    final bien = contrat.bien;

    return Card(
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // ── Titre + badge statut ──────────────────────────────────
              Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Expanded(
                    child: Text(
                      bien?.titre ?? 'Bien inconnu',
                      style: Theme.of(context).textTheme.titleMedium,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: _statutColor(contrat.statutContrat)
                          .withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                          color: _statutColor(contrat.statutContrat)),
                    ),
                    child: Text(
                      Formatters.statut(contrat.statutContrat),
                      style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w600,
                          color: _statutColor(contrat.statutContrat)),
                    ),
                  ),
                ],
              ),

              // ── Localisation ─────────────────────────────────────────
              if (bien != null) ...[
                const SizedBox(height: 6),
                Row(children: [
                  const Icon(Icons.location_on,
                      size: 13, color: AppColors.textSecondary),
                  const SizedBox(width: 3),
                  Expanded(
                    child: Text(
                      '${bien.localisation}, ${bien.ville}',
                      style: Theme.of(context).textTheme.bodyMedium,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ]),
              ],

              const SizedBox(height: 10),
              const Divider(height: 1),
              const SizedBox(height: 10),

              // ── Infos financières : 2 lignes de 2 colonnes ───────────
              Row(
                children: [
                  Expanded(
                    child: _infoItem(
                      context,
                      'Type',
                      Formatters.transaction(contrat.typeContrat),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _infoItem(
                      context,
                      'Total',
                      Formatters.prix(contrat.montantTotal),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  Expanded(
                    child: _infoItem(
                      context,
                      'Payé',
                      Formatters.prix(contrat.montantPaye),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _infoItem(
                      context,
                      'Solde',
                      Formatters.prix(contrat.soldeRestant),
                      valueColor: contrat.soldeRestant > 0
                          ? AppColors.error
                          : AppColors.success,
                    ),
                  ),
                ],
              ),

              // ── Date ─────────────────────────────────────────────────
              if (contrat.dateContrat != null) ...[
                const SizedBox(height: 8),
                Text(
                  'Date contrat : ${contrat.dateContrat}',
                  style: Theme.of(context).textTheme.labelSmall,
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _infoItem(BuildContext context, String label, String value,
      {Color? valueColor}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: Theme.of(context)
              .textTheme
              .labelSmall
              ?.copyWith(color: AppColors.textSecondary),
        ),
        const SizedBox(height: 2),
        Text(
          value,
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
          style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                fontWeight: FontWeight.w600,
                color: valueColor,
              ),
        ),
      ],
    );
  }
}
