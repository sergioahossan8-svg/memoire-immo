// lib/screens/biens/estimation_screen.dart
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/theme/app_theme.dart';
import '../../core/utils/formatters.dart';
import '../../models/notification_model.dart';
import '../../providers/bien_provider.dart';
import '../../services/estimation_service.dart';
import '../../widgets/common/custom_button.dart';
import '../../widgets/common/custom_text_field.dart';
import '../../widgets/common/loading_widget.dart';

class EstimationScreen extends ConsumerStatefulWidget {
  const EstimationScreen({super.key});

  @override
  ConsumerState<EstimationScreen> createState() => _EstimationScreenState();
}

class _EstimationScreenState extends ConsumerState<EstimationScreen> {
  final _formKey = GlobalKey<FormState>();
  final _villeCtrl = TextEditingController();
  final _superficieCtrl = TextEditingController();
  int? _selectedTypeId;
  String _transaction = 'location';
  int? _chambres;
  bool _isLoading = false;
  EstimationModel? _estimation;
  String? _message;

  @override
  void dispose() {
    _villeCtrl.dispose();
    _superficieCtrl.dispose();
    super.dispose();
  }

  Future<void> _estimer() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedTypeId == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(
          content: Text('Sélectionnez un type de bien')));
      return;
    }
    setState(() {
      _isLoading = true;
      _estimation = null;
      _message = null;
    });
    try {
      final result = await EstimationService().estimer(
        typeBienId: _selectedTypeId!,
        ville: _villeCtrl.text.trim(),
        superficie: double.parse(_superficieCtrl.text),
        transaction: _transaction,
        chambres: _chambres,
      );
      setState(() {
        _estimation = result['estimation'];
        _message = result['message'];
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
        _message = 'Erreur lors de l\'estimation.';
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final typesAsync = ref.watch(typesBiensProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Estimation de bien')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Intro
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppColors.secondary.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: AppColors.secondary.withOpacity(0.3)),
                ),
                child: Row(children: [
                  const Icon(Icons.info_outline, color: AppColors.secondary),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      'Obtenez une estimation basée sur les biens similaires dans notre base.',
                      style: Theme.of(context).textTheme.bodyMedium,
                    ),
                  ),
                ]),
              ),
              const SizedBox(height: 24),
              Text('Type de bien',
                  style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 8),
              typesAsync.when(
                data: (types) => DropdownButtonFormField<int>(
                  value: _selectedTypeId,
                  hint: const Text('Choisir le type'),
                  items: types.map((t) => DropdownMenuItem(
                          value: t.id, child: Text(t.libelle))).toList(),
                  onChanged: (v) => setState(() => _selectedTypeId = v),
                  validator: (v) => v == null ? 'Requis' : null,
                  decoration: InputDecoration(
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(12))),
                ),
                loading: () => const CircularProgressIndicator(),
                error: (_, __) =>
                    const Text('Impossible de charger les types'),
              ),
              const SizedBox(height: 16),
              Text('Transaction',
                  style: Theme.of(context).textTheme.titleMedium),
              const SizedBox(height: 8),
              Row(children: [
                Expanded(
                  child: GestureDetector(
                    onTap: () => setState(() => _transaction = 'location'),
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      decoration: BoxDecoration(
                        color: _transaction == 'location'
                            ? AppColors.primary
                            : Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: AppColors.primary),
                      ),
                      child: Center(
                        child: Text(
                          'Location',
                          style: TextStyle(
                              color: _transaction == 'location'
                                  ? Colors.white
                                  : AppColors.primary,
                              fontWeight: FontWeight.w600),
                        ),
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: GestureDetector(
                    onTap: () => setState(() => _transaction = 'vente'),
                    child: Container(
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      decoration: BoxDecoration(
                        color: _transaction == 'vente'
                            ? AppColors.primary
                            : Colors.white,
                        borderRadius: BorderRadius.circular(12),
                        border: Border.all(color: AppColors.primary),
                      ),
                      child: Center(
                        child: Text(
                          'Vente',
                          style: TextStyle(
                              color: _transaction == 'vente'
                                  ? Colors.white
                                  : AppColors.primary,
                              fontWeight: FontWeight.w600),
                        ),
                      ),
                    ),
                  ),
                ),
              ]),
              const SizedBox(height: 16),
              CustomTextField(
                controller: _villeCtrl,
                label: 'Ville',
                hint: 'ex: Cotonou',
                prefixIcon: const Icon(Icons.location_city_outlined),
                validator: (v) => v!.isEmpty ? 'Ville requise' : null,
              ),
              const SizedBox(height: 16),
              CustomTextField(
                controller: _superficieCtrl,
                label: 'Superficie (m²)',
                keyboardType: TextInputType.number,
                prefixIcon: const Icon(Icons.square_foot),
                validator: (v) {
                  if (v == null || v.isEmpty) return 'Requis';
                  if (double.tryParse(v) == null) return 'Nombre invalide';
                  return null;
                },
              ),
              const SizedBox(height: 32),
              CustomButton(
                label: 'Estimer mon bien',
                isLoading: _isLoading,
                icon: Icons.calculate,
                onPressed: _estimer,
              ),
              const SizedBox(height: 24),
              // Résultat
              if (_isLoading)
                const LoadingWidget(message: 'Calcul en cours...')
              else if (_estimation != null)
                _estimationResult(context, _estimation!)
              else if (_message != null)
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: AppColors.error.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                        color: AppColors.error.withOpacity(0.3)),
                  ),
                  child: Row(children: [
                    const Icon(Icons.warning_amber_rounded,
                        color: AppColors.error),
                    const SizedBox(width: 12),
                    Expanded(
                        child: Text(_message!,
                            style: Theme.of(context).textTheme.bodyMedium)),
                  ]),
                ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _estimationResult(
      BuildContext context, EstimationModel estimation) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: AppColors.success.withOpacity(0.08),
        borderRadius: BorderRadius.circular(16),
        border:
            Border.all(color: AppColors.success.withOpacity(0.3)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(children: [
            const Icon(Icons.check_circle, color: AppColors.success),
            const SizedBox(width: 8),
            Text('Estimation calculée',
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    color: AppColors.success)),
          ]),
          const SizedBox(height: 4),
          Text(
              'Basée sur ${estimation.nbBiens} bien(s) de référence à ${estimation.ville}',
              style: Theme.of(context).textTheme.bodyMedium),
          const Divider(height: 24),
          _ligne(context, 'Estimation basse',
              Formatters.prix(estimation.min)),
          const SizedBox(height: 8),
          _ligne(context, 'Estimation moyenne',
              Formatters.prix(estimation.moyen),
              bold: true),
          const SizedBox(height: 8),
          _ligne(context, 'Estimation haute',
              Formatters.prix(estimation.max)),
          const Divider(height: 24),
          _ligne(context, 'Prix au m²',
              '${estimation.prixM2.toStringAsFixed(0)} FCFA/m²'),
          const SizedBox(height: 4),
          _ligne(context, 'Superficie',
              '${estimation.superficie.toStringAsFixed(0)} m²'),
        ],
      ),
    );
  }

  Widget _ligne(BuildContext context, String label, String value,
      {bool bold = false}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: Theme.of(context).textTheme.bodyMedium),
        Text(
          value,
          style: Theme.of(context).textTheme.bodyLarge?.copyWith(
              fontWeight: bold ? FontWeight.bold : FontWeight.w500,
              color: bold ? AppColors.accent : AppColors.textPrimary),
        ),
      ],
    );
  }
}
