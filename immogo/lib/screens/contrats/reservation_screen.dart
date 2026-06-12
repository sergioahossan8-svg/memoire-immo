// lib/screens/contrats/reservation_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/theme/app_theme.dart';
import '../../core/utils/formatters.dart';
import '../../models/bien_model.dart';
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
  bool _isLoading = false;

  bool get _isComplet => widget.payerComplet;

  // Date limite = maintenant + 15 jours (définie côté client et envoyée au backend)
  DateTime get _dateLimiteFixe => DateTime.now().add(const Duration(days: 15));

  String _formatDateForApi(DateTime date) {
    return '${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')} '
        '${date.hour.toString().padLeft(2, '0')}:${date.minute.toString().padLeft(2, '0')}:${date.second.toString().padLeft(2, '0')}';
  }

  Future<void> _submit(double montantTotal) async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _isLoading = true);
    final double acompte = montantTotal * 0.10;
    try {
      if (_isComplet) {
        // Paiement complet (100% du montant total)
        if (_modePaiement == 'especes') {
          // Espèces : appel API qui crée le contrat en_attente sans KKiapay
          final result = await PaiementService().payerCompletEspeces(
            bienId: widget.bienId,
            typeContrat: _typeContrat,
          );
          if (mounted) {
            await _showEspecesCompletDialog(result, montantTotal);
          }
          return;
        }
        final kkiapayData = await PaiementService().payerComplet(
          bienId: widget.bienId,
          typeContrat: _typeContrat,
        );
        if (mounted) {
          context.push('/paiement/kkiapay', extra: kkiapayData);
        }
      } else {
        // Reservation (acompte 10% du montant total) — date limite = now + 15 jours
        final reservationResult = await ContratService().reserver(
          bienId: widget.bienId,
          typeContrat: _typeContrat,
          dateLimite: _formatDateForApi(_dateLimiteFixe),
          modePaiement: _modePaiement,
        );

        // CAS ESPÈCES : afficher une dialog avec les infos de l'agence
        if (_modePaiement == 'especes') {
          if (mounted) {
            await _showEspecesDialog(reservationResult);
          }
          return;
        }

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

  Future<void> _showEspecesCompletDialog(
      Map<String, dynamic> result, double montantTotal) async {
    final agence = result['agence'] as Map<String, dynamic>?;
    if (!mounted) return;
    await showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        shape:
            RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        contentPadding: EdgeInsets.zero,
        content: SingleChildScrollView(
          child: Column(mainAxisSize: MainAxisSize.min, children: [
            Container(
              width: double.infinity,
              padding:
                  const EdgeInsets.symmetric(vertical: 24, horizontal: 20),
              decoration: const BoxDecoration(
                color: Color(0xFFF59E0B),
                borderRadius:
                    BorderRadius.vertical(top: Radius.circular(20)),
              ),
              child: Column(children: [
                const Icon(Icons.store_outlined,
                    color: Colors.white, size: 44),
                const SizedBox(height: 8),
                const Text(
                  'Rendez-vous à l\'agence',
                  style: TextStyle(
                      color: Colors.white,
                      fontSize: 18,
                      fontWeight: FontWeight.bold),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 4),
                Text(
                  'À régler : ${Formatters.prix(montantTotal)}',
                  style:
                      const TextStyle(color: Colors.white70, fontSize: 13),
                ),
              ]),
            ),
            Padding(
              padding: const EdgeInsets.all(20),
              child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Contactez l\'agence du bien',
                      style: TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 15,
                          color: AppColors.textPrimary),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Payez le montant total en espèces directement au siège. '
                      'L\'agence confirmera votre paiement et le bien vous sera attribué.',
                      style: TextStyle(
                          fontSize: 12,
                          color: AppColors.textSecondary,
                          height: 1.5),
                    ),
                    if (agence != null) ...[
                      const SizedBox(height: 16),
                      if ((agence['nom'] ?? '').toString().isNotEmpty)
                        _agenceInfoRow(Icons.business_outlined, 'Agence',
                            agence['nom'].toString()),
                      if ((agence['telephone'] ?? '').toString().isNotEmpty) ...[
                        const SizedBox(height: 10),
                        _agenceInfoRow(Icons.phone_outlined, 'Téléphone',
                            agence['telephone'].toString()),
                      ],
                      if ((agence['whatsapp'] ?? '').toString().isNotEmpty) ...[
                        const SizedBox(height: 10),
                        _agenceInfoRow(Icons.chat_outlined, 'WhatsApp',
                            agence['whatsapp'].toString(),
                            color: const Color(0xFF25D366)),
                      ],
                      if ((agence['adresse'] ?? '').toString().isNotEmpty) ...[
                        const SizedBox(height: 10),
                        _agenceInfoRow(
                            Icons.location_on_outlined,
                            'Siège',
                            agence['adresse'].toString() +
                                ((agence['ville'] ?? '').toString().isNotEmpty
                                    ? ', ${agence['ville']}'
                                    : '')),
                      ] else if ((agence['ville'] ?? '').toString().isNotEmpty) ...[
                        const SizedBox(height: 10),
                        _agenceInfoRow(Icons.location_on_outlined, 'Ville',
                            agence['ville'].toString()),
                      ],
                    ],
                    const SizedBox(height: 16),
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFFF8E1),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: const Color(0xFFFFE082)),
                      ),
                      child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Icon(Icons.info_outline,
                                size: 16, color: Color(0xFFF59E0B)),
                            const SizedBox(width: 8),
                            const Expanded(
                              child: Text(
                                'Une fois votre paiement effectué, l\'agence confirmera et le bien disparaîtra du catalogue.',
                                style: TextStyle(
                                    fontSize: 11,
                                    color: Color(0xFF92400E),
                                    height: 1.5),
                              ),
                            ),
                          ]),
                    ),
                  ]),
            ),
          ]),
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.of(ctx).pop();
              context.go('/historique');
            },
            child: const Text('Voir mes demandes'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFF59E0B),
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () {
              Navigator.of(ctx).pop();
              context.go('/');
            },
            child:
                const Text('OK', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  Future<void> _showEspecesDialog(Map<String, dynamic> result) async {
    final agence = result['agence'] as Map<String, dynamic>?;
    final montantAcompte = (result['montant_acompte'] as num?)?.toDouble() ?? 0;
    final contratId = result['contrat_id'];

    if (!mounted) return;

    await showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        contentPadding: const EdgeInsets.all(0),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // En-tête vert
              Container(
                width: double.infinity,
                padding: const EdgeInsets.symmetric(vertical: 24, horizontal: 20),
                decoration: const BoxDecoration(
                  color: AppColors.success,
                  borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
                ),
                child: Column(children: [
                  const Icon(Icons.check_circle_outline,
                      color: Colors.white, size: 44),
                  const SizedBox(height: 8),
                  const Text(
                    'Réservation enregistrée !',
                    style: TextStyle(
                        color: Colors.white,
                        fontSize: 18,
                        fontWeight: FontWeight.bold),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 4),
                  Text(
                    'Acompte à régler : ${Formatters.prix(montantAcompte)}',
                    style: const TextStyle(
                        color: Colors.white70, fontSize: 13),
                  ),
                ]),
              ),
              // Corps
              Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Contactez l\'agence du bien',
                      style: TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 15,
                          color: AppColors.textPrimary),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Rendez-vous au siège de l\'agence pour régler votre acompte en espèces.',
                      style: TextStyle(
                          fontSize: 12,
                          color: AppColors.textSecondary,
                          height: 1.5),
                    ),
                    if (agence != null) ...[
                      const SizedBox(height: 16),
                      // Nom agence
                      _agenceInfoRow(
                        Icons.business_outlined,
                        'Agence',
                        agence['nom']?.toString() ?? '',
                      ),
                      // Téléphone
                      if ((agence['telephone'] ?? '').toString().isNotEmpty) ...[
                        const SizedBox(height: 10),
                        _agenceInfoRow(
                          Icons.phone_outlined,
                          'Téléphone',
                          agence['telephone'].toString(),
                        ),
                      ],
                      // WhatsApp
                      if ((agence['whatsapp'] ?? '').toString().isNotEmpty) ...[
                        const SizedBox(height: 10),
                        _agenceInfoRow(
                          Icons.chat_outlined,
                          'WhatsApp',
                          agence['whatsapp'].toString(),
                          color: const Color(0xFF25D366),
                        ),
                      ],
                      // Adresse / siège
                      if ((agence['adresse'] ?? '').toString().isNotEmpty) ...[
                        const SizedBox(height: 10),
                        _agenceInfoRow(
                          Icons.location_on_outlined,
                          'Siège',
                          agence['adresse'].toString() +
                              ((agence['ville'] ?? '').toString().isNotEmpty
                                  ? ', ${agence['ville']}'
                                  : ''),
                        ),
                      ] else if ((agence['ville'] ?? '').toString().isNotEmpty) ...[
                        const SizedBox(height: 10),
                        _agenceInfoRow(
                          Icons.location_on_outlined,
                          'Ville',
                          agence['ville'].toString(),
                        ),
                      ],
                    ],
                    const SizedBox(height: 16),
                    // Message d'instruction
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFFF8E1),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: const Color(0xFFFFE082)),
                      ),
                      child: Row(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Icon(Icons.info_outline,
                              size: 16, color: Color(0xFFF59E0B)),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Text(
                              'Mentionnez le numéro de réservation #$contratId. '
                              'Vous avez 15 jours pour effectuer ce paiement.',
                              style: const TextStyle(
                                  fontSize: 11,
                                  color: Color(0xFF92400E),
                                  height: 1.5),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () {
              Navigator.of(ctx).pop();
              context.go('/historique');
            },
            child: const Text('Voir mes réservations'),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.primary,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10)),
            ),
            onPressed: () {
              Navigator.of(ctx).pop();
              context.go('/');
            },
            child: const Text('OK', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }

  Widget _agenceInfoRow(IconData icon, String label, String value,
      {Color? color}) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 16, color: color ?? AppColors.primary),
        const SizedBox(width: 8),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(label,
                  style: const TextStyle(
                      fontSize: 10, color: AppColors.textSecondary)),
              Text(value,
                  style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: color ?? AppColors.textPrimary)),
            ],
          ),
        ),
      ],
    );
  }

  Widget _virementInfoWidget(
      BuildContext context, AgenceDetailModel agence, double montant) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color(0xFFEFF6FF),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFBFDBFE)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(children: [
            const Icon(Icons.account_balance_outlined,
                size: 16, color: Color(0xFF2563EB)),
            const SizedBox(width: 6),
            Text(
              'Coordonnées bancaires',
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  fontWeight: FontWeight.bold,
                  color: const Color(0xFF2563EB)),
            ),
          ]),
          const SizedBox(height: 10),
          if ((agence.banqueNom ?? '').isNotEmpty)
            _bankRow(context, 'Banque', agence.banqueNom!),
          if ((agence.banqueTitulaire ?? '').isNotEmpty) ...[
            const SizedBox(height: 6),
            _bankRow(context, 'Titulaire', agence.banqueTitulaire!),
          ],
          if ((agence.banqueIban ?? '').isNotEmpty) ...[
            const SizedBox(height: 6),
            _bankRow(context, 'IBAN / N° compte', agence.banqueIban!,
                mono: true),
          ],
          if ((agence.banqueSwift ?? '').isNotEmpty) ...[
            const SizedBox(height: 6),
            _bankRow(context, 'SWIFT / BIC', agence.banqueSwift!,
                mono: true),
          ],
          const Padding(
              padding: EdgeInsets.symmetric(vertical: 8),
              child: Divider(height: 1, color: Color(0xFFBFDBFE))),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('Montant à virer',
                  style: Theme.of(context)
                      .textTheme
                      .bodySmall
                      ?.copyWith(color: const Color(0xFF2563EB))),
              Text(
                Formatters.prix(montant),
                style: Theme.of(context).textTheme.titleSmall?.copyWith(
                    color: const Color(0xFF2563EB),
                    fontWeight: FontWeight.bold),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: const Color(0xFFFFF8E1),
              borderRadius: BorderRadius.circular(8),
              border: Border.all(color: const Color(0xFFFFE082)),
            ),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Icon(Icons.info_outline,
                    size: 14, color: Color(0xFFF59E0B)),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    'Après votre virement, présentez-vous à l\'agence ${agence.nom} avec votre reçu bancaire pour confirmation.',
                    style: const TextStyle(
                        fontSize: 11,
                        color: Color(0xFF92400E),
                        height: 1.4),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _bankRow(BuildContext context, String label, String value,
      {bool mono = false}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label,
            style: const TextStyle(
                fontSize: 11, color: AppColors.textSecondary)),
        const SizedBox(width: 8),
        Flexible(
          child: Text(
            value,
            textAlign: TextAlign.right,
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w600,
              color: AppColors.textPrimary,
              fontFamily: mono ? 'monospace' : null,
            ),
          ),
        ),
      ],
    );
  }

  Widget _conditionsLocationWidget(
      BuildContext context, BienModel bien, double montantTotal) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: AppColors.primary.withValues(alpha: 0.05),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.primary.withValues(alpha: 0.2)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(children: [
            const Icon(Icons.receipt_long_outlined,
                size: 15, color: AppColors.primary),
            const SizedBox(width: 6),
            Text(
              'Conditions de location',
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                  fontWeight: FontWeight.bold, color: AppColors.primary),
            ),
          ]),
          const SizedBox(height: 10),
          _condRow(context, 'Loyer / mois',
              bien.prixFormate.isNotEmpty ? bien.prixFormate : Formatters.prix(bien.prix)),
          const SizedBox(height: 6),
          _condRow(context, 'Avance (${bien.avanceMois ?? 1} mois)',
              Formatters.prix(bien.prix * (bien.avanceMois ?? 1).toDouble())),
          if ((bien.cautionEau ?? 0) > 0) ...[
            const SizedBox(height: 6),
            _condRow(context, 'Caution eau', Formatters.prix(bien.cautionEau!)),
          ],
          if ((bien.cautionElectricite ?? 0) > 0) ...[
            const SizedBox(height: 6),
            _condRow(context, 'Caution électricité',
                Formatters.prix(bien.cautionElectricite!)),
          ],
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 8),
            child: Divider(height: 1),
          ),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text('Total à régler',
                  style: Theme.of(context)
                      .textTheme
                      .bodyMedium
                      ?.copyWith(fontWeight: FontWeight.bold)),
              Text(
                Formatters.prix(montantTotal),
                style: Theme.of(context).textTheme.titleSmall?.copyWith(
                    color: AppColors.accent, fontWeight: FontWeight.bold),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _condRow(BuildContext context, String label, String value) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label,
            style: Theme.of(context)
                .textTheme
                .bodySmall
                ?.copyWith(color: AppColors.textSecondary)),
        Text(value,
            style: Theme.of(context)
                .textTheme
                .bodySmall
                ?.copyWith(fontWeight: FontWeight.w600)),
      ],
    );
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
            final bien = data['bien'] as BienModel;
            // Montant total selon les conditions définies par l'admin
            // Pour une location : (prix × avance_mois) + caution_eau + caution_electricite
            final double montantTotal = bien.montantTotal;
            final double acompte = montantTotal * 0.10;

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
                              'Loyer : ${bien.prixFormate.isNotEmpty ? bien.prixFormate : Formatters.prix(bien.prix)}',
                              style: Theme.of(context)
                                  .textTheme
                                  .titleMedium
                                  ?.copyWith(color: AppColors.accent),
                            ),
                          ],
                        ),
                      ),
                      // Conditions de location (si applicable)
                      if (bien.transaction == 'location' &&
                          bien.avanceMois != null) ...[
                        const SizedBox(height: 16),
                        _conditionsLocationWidget(context, bien, montantTotal),
                      ],                      const SizedBox(height: 16),
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
                                  ? Formatters.prix(montantTotal)
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
                        // Infos bancaires si virement sélectionné
                        if (_modePaiement == 'virement' &&
                            bien.agenceDetail != null &&
                            bien.agenceDetail!.hasBanque) ...[
                          _virementInfoWidget(context, bien.agenceDetail!, acompte),
                          const SizedBox(height: 14),
                        ],
                        // Message délai 15 jours
                        Container(
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                            color: AppColors.premium.withValues(alpha: 0.07),
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(
                                color: AppColors.premium.withValues(alpha: 0.3)),
                          ),
                          child: Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Icon(Icons.info_outline,
                                  size: 20, color: AppColors.premium),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      'Délai de paiement : 15 jours',
                                      style: Theme.of(context)
                                          .textTheme
                                          .bodyMedium
                                          ?.copyWith(
                                              color: AppColors.premium,
                                              fontWeight: FontWeight.w700),
                                    ),
                                    const SizedBox(height: 4),
                                    Text(
                                      'Vous avez 15 jours pour solder votre réservation. Passé ce délai, votre réservation sera annulée, le bien redeviendra disponible et votre argent vous sera restitué.',
                                      style: Theme.of(context)
                                          .textTheme
                                          .labelSmall
                                          ?.copyWith(
                                              color: AppColors.premium,
                                              height: 1.5),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                        ),
                      ] else ...[
                        // Mode de paiement pour le paiement complet
                        const SizedBox(height: 14),
                        Text('Mode de paiement',
                            style: Theme.of(context).textTheme.titleMedium),
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
                                value: 'carte', child: Text('Carte')),
                            DropdownMenuItem(
                                value: 'especes',
                                child: Text('Espèces (paiement sur place)')),
                          ],
                          onChanged: (v) => setState(() => _modePaiement = v!),
                          decoration: InputDecoration(
                            border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(12)),
                          ),
                        ),
                        const SizedBox(height: 14),
                        // Message selon le mode choisi
                        if (_modePaiement == 'especes') ...[
                          Container(
                            width: double.infinity,
                            padding: const EdgeInsets.all(14),
                            decoration: BoxDecoration(
                              color: const Color(0xFFFFF8E1),
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(
                                  color: const Color(0xFFFFE082)),
                            ),
                            child: Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Icon(Icons.money_outlined,
                                    color: Color(0xFFF59E0B), size: 22),
                                const SizedBox(width: 10),
                                Expanded(
                                  child: Text(
                                    'Rendez-vous au siège de l\'agence du bien pour régler le montant en espèces. '
                                    'L\'agence confirmera votre paiement et le bien vous sera attribué.',
                                    style: Theme.of(context)
                                        .textTheme
                                        .bodyMedium
                                        ?.copyWith(
                                            color: const Color(0xFF92400E),
                                            height: 1.5),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ] else ...[
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
                                    'Vous allez payer la totalité du bien de manière sécurisée.',
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
                      ],
                      const SizedBox(height: 28),
                      CustomButton(
                        label: _isComplet
                            ? (_modePaiement == 'especes'
                                ? 'Je vais payer sur place'
                                : 'Payer en totalité')
                            : 'Payer l\'acompte',
                        icon: _modePaiement == 'especes' && _isComplet
                            ? Icons.location_on_outlined
                            : Icons.payment,
                        backgroundColor: _modePaiement == 'especes' && _isComplet
                            ? const Color(0xFFF59E0B)
                            : null,
                        isLoading: _isLoading,
                        onPressed: _isLoading
                            ? null
                            : () => _submit(montantTotal),
                      ),
                      const SizedBox(height: 12),
                      Center(
                        child: Text(
                          _modePaiement == 'especes' && _isComplet
                              ? 'Paiement sur place — contactez l\'agence'
                              : 'Paiement 100% sécurisé',
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
