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
        // Paiement complet
        final result = await PaiementService().payerComplet(
          bienId: widget.bienId,
          typeContrat: _typeContrat,
        );
        if (mounted) {
          final url = result['payment_url'] as String?;
          if (url != null) {
            context.push(
                '/paiement/webview?url=${Uri.encodeComponent(url)}');
          }
        }
      } else {
        // Réservation (acompte 10%)
        await ContratService().reserver(
          bienId: widget.bienId,
          typeContrat: _typeContrat,
          dateLimite: _formatDateForApi(_dateLimite!),
          modePaiement: _modePaiement,
        );
        // Payer l'acompte via FedaPay
        // Ici on passe directement à payer-complet ou via session Laravel
        // Pour mobile, on utilise payer-complet avec montant 10%
        final acompte = bienPrix * 0.10;
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
                'Réservation initiée. Acompte à payer : ${Formatters.prix(acompte)}'),
            backgroundColor: AppColors.success,
          ),
        );
        context.go('/');
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

    return Scaffold(
      appBar: AppBar(title: const Text('Réservation')),
      body: bienAsync.when(
        loading: () => const LoadingWidget(),
        error: (e, _) =>
            const AppErrorWidget(message: 'Bien introuvable.'),
        data: (data) {
          final bien = data['bien'];
          final acompte = (bien.prix * 0.10) as double;

          return SingleChildScrollView(
            padding: const EdgeInsets.all(20),
            child: Form(
              key: _formKey,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Résumé bien
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: AppColors.primary.withOpacity(0.07),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(bien.titre,
                            style:
                                Theme.of(context).textTheme.titleLarge),
                        const SizedBox(height: 4),
                        Text(
                            '${bien.localisation}, ${bien.ville}',
                            style:
                                Theme.of(context).textTheme.bodyMedium),
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
                  const SizedBox(height: 24),
                  // Mode paiement
                  Row(children: [
                    Expanded(
                      child: GestureDetector(
                        onTap: () =>
                            setState(() => _payerComplet = false),
                        child: Container(
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: !_payerComplet
                                ? AppColors.primary
                                : Colors.white,
                            borderRadius: BorderRadius.circular(12),
                            border:
                                Border.all(color: AppColors.primary),
                          ),
                          child: Column(children: [
                            Icon(Icons.handshake_outlined,
                                color: !_payerComplet
                                    ? Colors.white
                                    : AppColors.primary),
                            const SizedBox(height: 4),
                            Text(
                              'Réservation\n(Acompte 10%)',
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                  color: !_payerComplet
                                      ? Colors.white
                                      : AppColors.primary,
                                  fontSize: 12,
                                  fontWeight: FontWeight.w600),
                            ),
                          ]),
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: GestureDetector(
                        onTap: () =>
                            setState(() => _payerComplet = true),
                        child: Container(
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: _payerComplet
                                ? AppColors.success
                                : Colors.white,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(
                                color: AppColors.success),
                          ),
                          child: Column(children: [
                            Icon(Icons.payments_outlined,
                                color: _payerComplet
                                    ? Colors.white
                                    : AppColors.success),
                            const SizedBox(height: 4),
                            Text(
                              'Paiement\ncomplet',
                              textAlign: TextAlign.center,
                              style: TextStyle(
                                  color: _payerComplet
                                      ? Colors.white
                                      : AppColors.success,
                                  fontSize: 12,
                                  fontWeight: FontWeight.w600),
                            ),
                          ]),
                        ),
                      ),
                    ),
                  ]),
                  const SizedBox(height: 20),
                  // Montant à payer
                  Container(
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: AppColors.accent.withOpacity(0.08),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(
                          color: AppColors.accent.withOpacity(0.3)),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                            _payerComplet
                                ? 'Montant total à payer :'
                                : 'Acompte à payer (10%) :',
                            style:
                                Theme.of(context).textTheme.bodyLarge),
                        Text(
                          _payerComplet
                              ? Formatters.prix(bien.prix as double)
                              : Formatters.prix(acompte),
                          style: Theme.of(context)
                              .textTheme
                              .titleLarge
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
                      style: Theme.of(context).textTheme.titleMedium),
                  const SizedBox(height: 8),
                  DropdownButtonFormField<String>(
                    value: _typeContrat,
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
                            borderRadius: BorderRadius.circular(12))),
                  ),
                  if (!_payerComplet) ...[
                    const SizedBox(height: 16),
                    Text('Mode de paiement',
                        style:
                            Theme.of(context).textTheme.titleMedium),
                    const SizedBox(height: 8),
                    DropdownButtonFormField<String>(
                      value: _modePaiement,
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
                              borderRadius: BorderRadius.circular(12))),
                    ),
                    const SizedBox(height: 16),
                    Text('Date limite de paiement du solde',
                        style:
                            Theme.of(context).textTheme.titleMedium),
                    const SizedBox(height: 8),
                    GestureDetector(
                      onTap: _pickDate,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                            horizontal: 16, vertical: 14),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(12),
                          border:
                              Border.all(color: AppColors.divider),
                        ),
                        child: Row(
                          mainAxisAlignment:
                              MainAxisAlignment.spaceBetween,
                          children: [
                            Text(_dateLimiteStr,
                                style: Theme.of(context)
                                    .textTheme
                                    .bodyLarge
                                    ?.copyWith(
                                        color: _dateLimite == null
                                            ? AppColors.textSecondary
                                            : AppColors.textPrimary)),
                            const Icon(Icons.calendar_today,
                                color: AppColors.primary),
                          ],
                        ),
                      ),
                    ),
                  ],
                  const SizedBox(height: 32),
                  CustomButton(
                    label: _payerComplet
                        ? 'Payer en totalité via FedaPay'
                        : 'Payer l\'acompte via FedaPay',
                    icon: Icons.payment,
                    isLoading: _isLoading,
                    onPressed: () => _submit(bien.prix as double),
                  ),
                  const SizedBox(height: 16),
                  Text(
                    'Vous serez redirigé vers la plateforme de paiement sécurisée FedaPay.',
                    textAlign: TextAlign.center,
                    style: Theme.of(context).textTheme.bodyMedium,
                  ),
                  const SizedBox(height: 32),
                ],
              ),
            ),
          );
        },
      ),
    );
  }
}
