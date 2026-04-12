// lib/screens/contrats/contrat_detail_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/theme/app_theme.dart';
import '../../core/utils/formatters.dart';
import '../../models/contrat_model.dart';
import '../../models/paiement_model.dart';
import '../../providers/contrat_provider.dart';
import '../../services/paiement_service.dart';
import '../../widgets/common/custom_button.dart';
import '../../widgets/common/loading_widget.dart';

class ContratDetailScreen extends ConsumerWidget {
  final int id;

  const ContratDetailScreen({super.key, required this.id});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final contratAsync = ref.watch(contratDetailProvider(id));

    return Scaffold(
      appBar: AppBar(title: const Text('Détail du contrat')),
      body: contratAsync.when(
        loading: () => const LoadingWidget(message: 'Chargement...'),
        error: (e, _) => AppErrorWidget(
          message: 'Impossible de charger ce contrat.',
          onRetry: () => ref.refresh(contratDetailProvider(id)),
        ),
        data: (contrat) => _buildContent(context, ref, contrat),
      ),
    );
  }

  Widget _buildContent(
      BuildContext context, WidgetRef ref, ContratModel contrat) {
    final bien = contrat.bien;
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Bien info
          if (bien != null) _bienCard(context, bien),
          const SizedBox(height: 20),
          // Statut général
          _statutCard(context, contrat),
          const SizedBox(height: 20),
          // Montants
          _montantsCard(context, contrat),
          const SizedBox(height: 20),
          // Paiements
          if (contrat.paiements != null && contrat.paiements!.isNotEmpty) ...[
            Text('Historique des paiements',
                style: Theme.of(context).textTheme.titleLarge),
            const SizedBox(height: 12),
            ...contrat.paiements!.map((p) => _paiementItem(context, p)),
          ],
          // Payer le solde
          if (contrat.soldeRestant > 0 &&
              contrat.statutContrat != 'annule') ...[
            const SizedBox(height: 20),
            const Divider(),
            const SizedBox(height: 16),
            Text('Payer le solde',
                style: Theme.of(context).textTheme.titleLarge),
            const SizedBox(height: 8),
            Text(
              'Solde restant : ${Formatters.prix(contrat.soldeRestant)}',
              style: Theme.of(context)
                  .textTheme
                  .bodyLarge
                  ?.copyWith(color: AppColors.error, fontWeight: FontWeight.w600),
            ),
            const SizedBox(height: 16),
            CustomButton(
              label: 'Payer le solde',
              icon: Icons.payment,
              onPressed: () =>
                  _initPayerSolde(context, ref, contrat),
            ),
          ],
          const SizedBox(height: 32),
        ],
      ),
    );
  }

  Widget _bienCard(BuildContext context, ContratBienModel bien) =>
      Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: AppColors.divider),
        ),
        child: Row(children: [
          const Icon(Icons.home_work_rounded,
              size: 40, color: AppColors.primary),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(bien.titre,
                    style: Theme.of(context).textTheme.titleMedium,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis),
                const SizedBox(height: 4),
                Text('${bien.localisation}, ${bien.ville}',
                    style: Theme.of(context).textTheme.bodyMedium),
              ],
            ),
          ),
          GestureDetector(
            onTap: () => GoRouter.of(context).push('/biens/${bien.id}'),
            child:
                const Icon(Icons.chevron_right, color: AppColors.primary),
          ),
        ]),
      );

  Widget _statutCard(BuildContext context, ContratModel contrat) {
    final color = _statutColor(contrat.statutContrat);
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: color.withValues(alpha: 0.4)),
      ),
      child: Row(children: [
        Icon(_statutIcon(contrat.statutContrat), color: color, size: 28),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(Formatters.statut(contrat.statutContrat),
                  style: Theme.of(context)
                      .textTheme
                      .titleMedium
                      ?.copyWith(color: color)),
              Text(Formatters.transaction(contrat.typeContrat),
                  style: Theme.of(context).textTheme.bodyMedium),
              if (contrat.dateContrat != null)
                Text('Le ${contrat.dateContrat}',
                    style: Theme.of(context).textTheme.labelSmall),
            ],
          ),
        ),
      ]),
    );
  }

  Widget _montantsCard(BuildContext context, ContratModel contrat) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.divider),
      ),
      child: Column(
        children: [
          _montantRow(context, 'Montant total',
              Formatters.prix(contrat.montantTotal), bold: true),
          const Divider(height: 20),
          _montantRow(context, 'Acompte (10%)',
              Formatters.prix(contrat.montantAcompte)),
          const SizedBox(height: 8),
          _montantRow(context, 'Montant payé',
              Formatters.prix(contrat.montantPaye),
              color: AppColors.success),
          const SizedBox(height: 8),
          _montantRow(context, 'Solde restant',
              Formatters.prix(contrat.soldeRestant),
              color: contrat.soldeRestant > 0
                  ? AppColors.error
                  : AppColors.success,
              bold: true),
        ],
      ),
    );
  }

  Widget _montantRow(BuildContext context, String label, String value,
      {bool bold = false, Color? color}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Flexible(
          child: Text(label,
              style: Theme.of(context).textTheme.bodyLarge,
              overflow: TextOverflow.ellipsis),
        ),
        const SizedBox(width: 8),
        Text(
          value,
          style: Theme.of(context).textTheme.bodyLarge?.copyWith(
              fontWeight: bold ? FontWeight.bold : FontWeight.w500,
              color: color),
        ),
      ],
    );
  }

  Widget _paiementItem(BuildContext context, PaiementModel p) {
    final isConfirme = p.statut == 'confirme';
    return Container(
      margin: const EdgeInsets.only(bottom: 8),
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: isConfirme
            ? AppColors.success.withValues(alpha: 0.07)
            : Colors.grey[50],
        borderRadius: BorderRadius.circular(10),
        border: Border.all(
            color: isConfirme
                ? AppColors.success.withValues(alpha: 0.3)
                : AppColors.divider),
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                    '${p.typePaiement.toUpperCase()} — ${p.modePaiement ?? ''}',
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                        fontWeight: FontWeight.w600),
                    overflow: TextOverflow.ellipsis),
                if (p.reference != null)
                  Text('Réf: ${p.reference}',
                      style: Theme.of(context).textTheme.labelSmall,
                      overflow: TextOverflow.ellipsis),
                if (p.datePaiement != null)
                  Text(p.datePaiement!,
                      style: Theme.of(context).textTheme.labelSmall),
              ],
            ),
          ),
          const SizedBox(width: 8),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(Formatters.prix(p.montant),
                  style: Theme.of(context).textTheme.bodyLarge?.copyWith(
                      fontWeight: FontWeight.bold,
                      color: isConfirme
                          ? AppColors.success
                          : AppColors.textSecondary)),
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                decoration: BoxDecoration(
                  color: isConfirme
                      ? AppColors.success.withValues(alpha: 0.15)
                      : Colors.grey[200],
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  isConfirme ? 'Confirmé' : p.statut,
                  style: TextStyle(
                      fontSize: 10,
                      color: isConfirme
                          ? AppColors.success
                          : AppColors.textSecondary),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

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

  IconData _statutIcon(String s) {
    switch (s) {
      case 'actif':
        return Icons.check_circle;
      case 'en_attente':
        return Icons.hourglass_empty;
      case 'annule':
        return Icons.cancel;
      default:
        return Icons.info_outline;
    }
  }

  Future<void> _initPayerSolde(
      BuildContext context, WidgetRef ref, ContratModel contrat) async {
    final montant = contrat.soldeRestant;
    try {
      final result = await PaiementService().payerSolde(
        contratId: contrat.id,
        montant: montant,
        typePaiement: 'solde',
      );
      if (context.mounted) {
        final url = result['payment_url'] as String?;
        if (url != null) {
          context.push(
              '/paiement/webview?url=${Uri.encodeComponent(url)}&contrat_id=${contrat.id}');
        }
      }
    } catch (e) {
      if (context.mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
              content: Text('Erreur: ${e.toString()}'),
              backgroundColor: AppColors.error),
        );
      }
    }
  }
}
