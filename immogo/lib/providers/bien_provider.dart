// lib/providers/bien_provider.dart
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/bien_model.dart';
import '../services/bien_service.dart';

// Filtres
class BienFiltres {
  final String? transaction;
  final int? typeBienId;
  final String? ville;
  final double? prixMin;
  final double? prixMax;
  final String? search;

  const BienFiltres({
    this.transaction,
    this.typeBienId,
    this.ville,
    this.prixMin,
    this.prixMax,
    this.search,
  });

  BienFiltres copyWith({
    String? transaction,
    int? typeBienId,
    String? ville,
    double? prixMin,
    double? prixMax,
    String? search,
    bool clearTransaction = false,
    bool clearVille = false,
    bool clearSearch = false,
  }) {
    return BienFiltres(
      transaction: clearTransaction ? null : (transaction ?? this.transaction),
      typeBienId: typeBienId ?? this.typeBienId,
      ville: clearVille ? null : (ville ?? this.ville),
      prixMin: prixMin ?? this.prixMin,
      prixMax: prixMax ?? this.prixMax,
      search: clearSearch ? null : (search ?? this.search),
    );
  }
}

class BienListState {
  final List<BienModel> biens;
  final bool isLoading;
  final String? error;
  final int currentPage;
  final int lastPage;
  final BienFiltres filtres;

  const BienListState({
    this.biens = const [],
    this.isLoading = false,
    this.error,
    this.currentPage = 1,
    this.lastPage = 1,
    this.filtres = const BienFiltres(),
  });

  bool get hasMore => currentPage < lastPage;

  BienListState copyWith({
    List<BienModel>? biens,
    bool? isLoading,
    String? error,
    int? currentPage,
    int? lastPage,
    BienFiltres? filtres,
  }) {
    return BienListState(
      biens: biens ?? this.biens,
      isLoading: isLoading ?? this.isLoading,
      error: error,
      currentPage: currentPage ?? this.currentPage,
      lastPage: lastPage ?? this.lastPage,
      filtres: filtres ?? this.filtres,
    );
  }
}

class BienListNotifier extends StateNotifier<BienListState> {
  final BienService _service;

  BienListNotifier(this._service) : super(const BienListState());

  Future<void> load({bool refresh = false}) async {
    if (state.isLoading) return;
    final page = refresh ? 1 : state.currentPage;
    state = state.copyWith(isLoading: true, error: null);

    try {
      final result = await _service.getBiens(
        transaction: state.filtres.transaction,
        typeBienId: state.filtres.typeBienId,
        ville: state.filtres.ville,
        prixMin: state.filtres.prixMin,
        prixMax: state.filtres.prixMax,
        search: state.filtres.search,
        page: page,
      );
      final newBiens = result['biens'] as List<BienModel>;
      final pagination = result['pagination'] as Map<String, dynamic>;

      state = state.copyWith(
        biens: refresh ? newBiens : [...state.biens, ...newBiens],
        isLoading: false,
        currentPage: pagination['current_page'] as int,
        lastPage: pagination['last_page'] as int,
      );
    } catch (e) {
      state =
          state.copyWith(isLoading: false, error: 'Erreur de chargement.');
    }
  }

  Future<void> loadMore() async {
    if (!state.hasMore || state.isLoading) return;
    state = state.copyWith(currentPage: state.currentPage + 1);
    await load();
  }

  Future<void> applyFiltres(BienFiltres filtres) async {
    state = BienListState(filtres: filtres);
    await load(refresh: true);
  }

  Future<void> refresh() => load(refresh: true);
}

final bienServiceProvider =
    Provider<BienService>((ref) => BienService());

final bienListProvider =
    StateNotifierProvider<BienListNotifier, BienListState>((ref) {
  return BienListNotifier(ref.read(bienServiceProvider));
});

// Détail bien
final bienDetailProvider =
    FutureProvider.family<Map<String, dynamic>, int>((ref, id) async {
  return ref.read(bienServiceProvider).getBien(id);
});

// Types & villes
final typesBiensProvider = FutureProvider<List<TypeBienModel>>((ref) async {
  return ref.read(bienServiceProvider).getTypes();
});

final villesProvider = FutureProvider<List<String>>((ref) async {
  return ref.read(bienServiceProvider).getVilles();
});
