// lib/providers/contrat_provider.dart
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/contrat_model.dart';
import '../services/contrat_service.dart';

class ContratListNotifier
    extends StateNotifier<AsyncValue<List<ContratModel>>> {
  final ContratService _service;

  ContratListNotifier(this._service) : super(const AsyncValue.loading());

  Future<void> load() async {
    state = const AsyncValue.loading();
    try {
      final contrats = await _service.getHistorique();
      state = AsyncValue.data(contrats);
    } catch (e, s) {
      state = AsyncValue.error(e, s);
    }
  }
}

final contratServiceProvider =
    Provider<ContratService>((ref) => ContratService());

final contratListProvider =
    StateNotifierProvider<ContratListNotifier, AsyncValue<List<ContratModel>>>(
        (ref) {
  return ContratListNotifier(ref.read(contratServiceProvider));
});

final contratDetailProvider =
    FutureProvider.family<ContratModel, int>((ref, id) async {
  return ref.read(contratServiceProvider).getContrat(id);
});
