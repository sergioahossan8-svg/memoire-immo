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
      case 'actif':
        return AppColors.success;
      case 'en_attente':
        return AppColors.premium;
      case 'annule':
        return AppColors.error;
      default:
        return AppColors.textSecondary;
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
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Expanded(
                    child: Text(
                      bien?.titre ?? 'Bien inconnu',
                      style: Theme.of(context).textTheme.titleMedium,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: _statutColor(contrat.statutContrat)
                          .withOpacity(0.1),
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
              const SizedBox(height: 8),
              if (bien != null)
                Row(children: [
                  const Icon(Icons.location_on,
                      size: 14, color: AppColors.textSecondary),
                  const SizedBox(width: 4),
                  Text('${bien.localisation}, ${bien.ville}',
                      style: Theme.of(context).textTheme.bodyMedium),
                ]),
              const SizedBox(height: 12),
              const Divider(),
              const SizedBox(height: 8),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  _infoItem(context, 'Type',
                      Formatters.transaction(contrat.typeContrat)),
                  _infoItem(context, 'Total',
                      Formatters.prix(contrat.montantTotal)),
                  _infoItem(context, 'Payé',
                      Formatters.prix(contrat.montantPaye)),
                  _infoItem(context, 'Solde',
                      Formatters.prix(contrat.soldeRestant),
                      valueColor: contrat.soldeRestant > 0
                          ? AppColors.error
                          : AppColors.success),
                ],
              ),
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
        Text(label,
            style: Theme.of(context)
                .textTheme
                .labelSmall
                ?.copyWith(color: AppColors.textSecondary)),
        Text(value,
            style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                fontWeight: FontWeight.w600, color: valueColor)),
      ],
    );
  }
}
