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
  final bool payerComplet; // true = paiement complet, false = reservation (acompte)

  const ReservationScreen({
    super.key,
    required this.bienId,
    this.payerComplet = false,
  });

  @override
  ConsumerState<ReservationScreen> createState() =>
      _ReservationScreenState();
}

class _ReservationScreenState extends ConsumerState<ReservationScreen> {
  final _formKey = GlobalKey<FormState>();
  late String _typeContrat; // Defini par le type de transaction du bien
  String _modePaiement = 'mobile_money';
  DateTime? _dateLimite;
  bool _isLoading = false;

  bool get _isComplet => widget.payerComplet;

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
    if (_dateLimite == null && !_isComplet) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Sélectionnez une date limite de paiement')));
      return;
    }
    setState(() => _isLoading = true);
    try {
      if (_isComplet) {
        // Paiement complet (100%)
        final kkiapayData = await PaiementService().payerComplet(
          bienId: widget.bienId,
          typeContrat: _typeContrat,
        );
        if (mounted) {
          context.push('/paiement/kkiapay', extra: kkiapayData);
        }
      } else {
        // Reservation (acompte 10%)
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
      appBar: AppBar(
        title: Text(_isComplet ? 'Paiement en totalité' : 'Réservation'),
      ),
      body: SafeArea(
        child: bienAsync.when(
          loading: () => const LoadingWidget(),
          error: (e, _) =>
              const AppErrorWidget(message: 'Bien introuvable.'),
          data: (data) {
            final bien = data['bien'];
            final acompte = (bien.prix * 0.10) as double;

            // Recuperer le type de contrat du bien (non modifiable par le client)
            _typeContrat = bien.transaction;

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
                                _isComplet
                                    ? 'Montant total :'
                                    : 'Acompte (10%) :',
                                style: Theme.of(context)
                                    .textTheme
                                    .bodyLarge,
                              ),
                            ),
                            const SizedBox(width: 8),
                            Text(
                              _isComplet
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
                      // Type contrat (NON MODIFIABLE - defini par l'admin)
                      Text('Type de contrat',
                          style:
                              Theme.of(context).textTheme.titleMedium),
                      const SizedBox(height: 8),
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.symmetric(
                            horizontal: 16, vertical: 14),
                        decoration: BoxDecoration(
                          color: AppColors.primary.withValues(alpha: 0.07),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                              color: AppColors.primary.withValues(alpha: 0.2)),
                        ),
                        child: Row(
                          children: [
                            Icon(
                              _typeContrat == 'vente'
                                  ? Icons.home_work_outlined
                                  : Icons.key_outlined,
                              color: AppColors.primary,
                              size: 22,
                            ),
                            const SizedBox(width: 10),
                            Text(
                              Formatters.transaction(_typeContrat),
                              style: Theme.of(context)
                                  .textTheme
                                  .titleMedium
                                  ?.copyWith(
                                      color: AppColors.primary,
                                      fontWeight: FontWeight.w600),
                            ),
                            const Spacer(),
                            Text(
                              'Non modifiable',
                              style: Theme.of(context)
                                  .textTheme
                                  .labelSmall
                                  ?.copyWith(
                                      color: AppColors.textSecondary,
                                      fontStyle: FontStyle.italic),
                            ),
                          ],
                        ),
                      ),
                      if (!_isComplet) ...[
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
                      ] else ...[
                        // Message pour le paiement complet
                        const SizedBox(height: 14),
                        Container(
                          width: double.infinity,
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: AppColors.success.withValues(alpha: 0.08),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(
                                color: AppColors.success.withValues(alpha: 0.3)),
                          ),
                          child: Row(
                            children: [
                              const Icon(Icons.check_circle_outline,
                                  color: AppColors.success, size: 22),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Text(
                                  'Vous allez payer la totalité du bien via KKiapay.',
                                  style: Theme.of(context)
                                      .textTheme
                                      .bodyMedium
                                      ?.copyWith(color: AppColors.success),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                      const SizedBox(height: 28),
                      CustomButton(
                        label: _isComplet
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
}
