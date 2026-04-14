// lib/screens/contrats/reservation_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/theme/app_theme.dart';
import '../../core/utils/formatters.dart';
import '../../providers/bien_provider.dart';
import '../../services/contrat_service.dart';
import '../../services/paiement_service.dart';
import '../../widgets/common/custom_button.dart';
import '../../widgets/common/loading_widget.dart';

class ReservationScreen extends ConsumerStatefulWidget {
  final int bienId;

  const ReservationScreen({super.key, required this.bienId});

  @override
  ConsumerState<ReservationScreen> createState() =>
      _ReservationScreenState();
}

class _ReservationScreenState extends ConsumerState<ReservationScreen> {
  final _formKey = GlobalKey<FormState>();
  String _typeContrat = 'location';
  String _modePaiement = 'mobile_money';
  DateTime? _dateLimite;
  bool _isLoading = false;
  bool _payerComplet = false;

  String get _dateLimiteStr => _dateLimite == null
      ? 'Sélectionner une date'
      : '${_dateLimite!.day.toString().padLeft(2, '0')}/${_dateLimite!.month.toString().padLeft(2, '0')}/${_dateLimite!.year}';

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: DateTime.now().add(const Duration(days: 30)),
      firstDate: DateTime.now().add(const Duration(days: 1)),
      lastDate: DateTime.now().add(const Duration(days: 365)),
    );
    if (picked != null) setState(() => _dateLimite = picked);
  }

  String _formatDateForApi(DateTime date) {
    return '${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}';
  }

  Future<void> _submit(double bienPrix) async {
    if (!_formKey.currentState!.validate()) return;
    if (_dateLimite == null && !_payerComplet) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Sélectionnez une date limite de paiement')));
      return;
    }
    setState(() => _isLoading = true);
    try {
      if (_payerComplet) {
        final kkiapayData = await PaiementService().payerComplet(
          bienId: widget.bienId,
          typeContrat: _typeContrat,
        );
        if (mounted) {
          context.push('/paiement/kkiapay', extra: kkiapayData);
        }
      } else {
        final reservationResult = await ContratService().reserver(
          bienId: widget.bienId,
          typeContrat: _typeContrat,
          dateLimite: _formatDateForApi(_dateLimite!),
          modePaiement: _modePaiement,
        );
        final reservationKey =
            reservationResult['reservation_key'] as String?;
        if (reservationKey != null) {
          final kkiapayData = await PaiementService().initReservation(
            bienId: widget.bienId,
            reservationKey: reservationKey,
          );
          if (mounted) {
            context.push('/paiement/kkiapay', extra: kkiapayData);
          }
        } else {
          final acompte = bienPrix * 0.10;
          if (mounted) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(
                    'Réservation initiée. Acompte : ${Formatters.prix(acompte)}'),
                backgroundColor: AppColors.success,
              ),
            );
            context.go('/');
          }
        }
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
              content: Text('Erreur: ${e.toString()}'),
              backgroundColor: AppColors.error),
        );
      }
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final bienAsync = ref.watch(bienDetailProvider(widget.bienId));
    final size = MediaQuery.of(context).size;

    return Scaffold(
      appBar: AppBar(title: const Text('Réservation')),
      body: SafeArea(
        child: bienAsync.when(
          loading: () => const LoadingWidget(),
          error: (e, _) =>
              const AppErrorWidget(message: 'Bien introuvable.'),
          data: (data) {
            final bien = data['bien'];
            final acompte = (bien.prix * 0.10) as double;

            return GestureDetector(
              onTap: () => FocusScope.of(context).unfocus(),
              child: SingleChildScrollView(
                physics: const ClampingScrollPhysics(),
                padding: EdgeInsets.symmetric(
                  horizontal: size.width * 0.05,
                  vertical: 16,
                ),
                child: Form(
                  key: _formKey,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Résumé bien
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: AppColors.primary.withValues(alpha: 0.07),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              bien.titre,
                              style: Theme.of(context).textTheme.titleLarge,
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                            ),
                            const SizedBox(height: 4),
                            Text(
                              '${bien.localisation}, ${bien.ville}',
                              style: Theme.of(context).textTheme.bodyMedium,
                            ),
                            const SizedBox(height: 8),
                            Text(
                              'Prix total : ${Formatters.prix(bien.prix)}',
                              style: Theme.of(context)
                                  .textTheme
                                  .titleMedium
                                  ?.copyWith(color: AppColors.accent),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 20),
                      // Mode (acompte / complet)
                      Row(children: [
                        Expanded(
                          child: _modeOption(
                            label: 'Réservation\n(Acompte 10%)',
                            icon: Icons.handshake_outlined,
                            selected: !_payerComplet,
                            color: AppColors.primary,
                            onTap: () =>
                                setState(() => _payerComplet = false),
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: _modeOption(
                            label: 'Paiement\ncomplet',
                            icon: Icons.payments_outlined,
                            selected: _payerComplet,
                            color: AppColors.success,
                            onTap: () =>
                                setState(() => _payerComplet = true),
                          ),
                        ),
                      ]),
                      const SizedBox(height: 16),
                      // Montant à payer
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.symmetric(
                            horizontal: 16, vertical: 14),
                        decoration: BoxDecoration(
                          color:
                              AppColors.accent.withValues(alpha: 0.08),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                              color:
                                  AppColors.accent.withValues(alpha: 0.3)),
                        ),
                        child: Row(
                          mainAxisAlignment:
                              MainAxisAlignment.spaceBetween,
                          children: [
                            Flexible(
                              child: Text(
                                _payerComplet
                                    ? 'Montant total :'
                                    : 'Acompte (10%) :',
                                style: Theme.of(context)
                                    .textTheme
                                    .bodyLarge,
                              ),
                            ),
                            const SizedBox(width: 8),
                            Text(
                              _payerComplet
                                  ? Formatters.prix(bien.prix as double)
                                  : Formatters.prix(acompte),
                              style: Theme.of(context)
                                  .textTheme
                                  .titleMedium
                                  ?.copyWith(
                                      color: AppColors.accent,
                                      fontWeight: FontWeight.bold),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 20),
                      // Type contrat
                      Text('Type de contrat',
                          style:
                              Theme.of(context).textTheme.titleMedium),
                      const SizedBox(height: 8),
                      DropdownButtonFormField<String>(
                        value: _typeContrat,
                        isExpanded: true,
                        items: const [
                          DropdownMenuItem(
                              value: 'location', child: Text('Location')),
                          DropdownMenuItem(
                              value: 'vente', child: Text('Vente')),
                        ],
                        onChanged: (v) =>
                            setState(() => _typeContrat = v!),
                        decoration: InputDecoration(
                          border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(12)),
                        ),
                      ),
                      if (!_payerComplet) ...[
                        const SizedBox(height: 14),
                        Text('Mode de paiement',
                            style: Theme.of(context)
                                .textTheme
                                .titleMedium),
                        const SizedBox(height: 8),
                        DropdownButtonFormField<String>(
                          value: _modePaiement,
                          isExpanded: true,
                          items: const [
                            DropdownMenuItem(
                                value: 'mobile_money',
                                child: Text('Mobile Money')),
                            DropdownMenuItem(
                                value: 'virement',
                                child: Text('Virement')),
                            DropdownMenuItem(
                                value: 'especes',
                                child: Text('Espèces')),
                            DropdownMenuItem(
                                value: 'carte', child: Text('Carte')),
                          ],
                          onChanged: (v) =>
                              setState(() => _modePaiement = v!),
                          decoration: InputDecoration(
                            border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12)),
                          ),
                        ),
                        const SizedBox(height: 14),
                        Text('Date limite de paiement du solde',
                            style: Theme.of(context)
                                .textTheme
                                .titleMedium),
                        const SizedBox(height: 8),
                        GestureDetector(
                          onTap: _pickDate,
                          child: Container(
                            width: double.infinity,
                            padding: const EdgeInsets.symmetric(
                                horizontal: 16, vertical: 14),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(
                                  color: AppColors.divider),
                            ),
                            child: Row(
                              mainAxisAlignment:
                                  MainAxisAlignment.spaceBetween,
                              children: [
                                Flexible(
                                  child: Text(
                                    _dateLimiteStr,
                                    style: Theme.of(context)
                                        .textTheme
                                        .bodyLarge
                                        ?.copyWith(
                                            color: _dateLimite == null
                                                ? AppColors.textSecondary
                                                : AppColors.textPrimary),
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                                const Icon(Icons.calendar_today,
                                    size: 20, color: AppColors.primary),
                              ],
                            ),
                          ),
                        ),
                      ],
                      const SizedBox(height: 28),
                      CustomButton(
                        label: _payerComplet
                            ? 'Payer en totalité via KKiapay'
                            : 'Payer l\'acompte via KKiapay',
                        icon: Icons.payment,
                        isLoading: _isLoading,
                        onPressed: _isLoading
                            ? null
                            : () => _submit(bien.prix as double),
                      ),
                      const SizedBox(height: 12),
                      Center(
                        child: Text(
                          'Paiement sécurisé via KKiapay',
                          textAlign: TextAlign.center,
                          style: Theme.of(context)
                              .textTheme
                              .bodyMedium
                              ?.copyWith(fontSize: 12),
                        ),
                      ),
                      const SizedBox(height: 32),
                    ],
                  ),
                ),
              ),
            );
          },
        ),
      ),
    );
  }

  Widget _modeOption({
    required String label,
    required IconData icon,
    required bool selected,
    required Color color,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 8),
        decoration: BoxDecoration(
          color: selected ? color : Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: color, width: selected ? 2 : 1.5),
          boxShadow: selected
              ? [
                  BoxShadow(
                    color: color.withValues(alpha: 0.2),
                    blurRadius: 8,
                    offset: const Offset(0, 4),
                  )
                ]
              : null,
        ),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          Icon(icon, color: selected ? Colors.white : color, size: 26),
          const SizedBox(height: 6),
          Text(
            label,
            textAlign: TextAlign.center,
            style: TextStyle(
              color: selected ? Colors.white : color,
              fontSize: 12,
              fontWeight: FontWeight.w600,
            ),
          ),
        ]),
      ),
    );
  }
}
