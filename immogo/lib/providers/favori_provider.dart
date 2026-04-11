// lib/providers/favori_provider.dart
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/bien_model.dart';
import '../services/favori_service.dart';

class FavoriNotifier extends StateNotifier<AsyncValue<List<BienModel>>> {
  final FavoriService _service;

  FavoriNotifier(this._service) : super(const AsyncValue.loading());

  Future<void> load() async {
    state = const AsyncValue.loading();
    try {
      final favoris = await _service.getFavoris();
      state = AsyncValue.data(favoris);
    } catch (e, s) {
      state = AsyncValue.error(e, s);
    }
  }

  Future<bool> toggle(int bienId) async {
    try {
      final added = await _service.toggle(bienId);
      await load();
      return added;
    } catch (_) {
      return false;
    }
  }

  bool isFavori(int bienId) {
    return state.valueOrNull?.any((b) => b.id == bienId) ?? false;
  }
}

final favoriServiceProvider =
    Provider<FavoriService>((ref) => FavoriService());

final favoriProvider =
    StateNotifierProvider<FavoriNotifier, AsyncValue<List<BienModel>>>(
        (ref) {
  return FavoriNotifier(ref.read(favoriServiceProvider));
});
