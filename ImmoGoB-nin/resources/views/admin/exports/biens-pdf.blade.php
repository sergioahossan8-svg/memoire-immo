<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Rapport - {{ $agence->nom_commercial }}</title>
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:Arial,sans-serif;font-size:11px;color:#1f2937;padding:20px}
h1{color:#06b6d4;font-size:18px;margin-bottom:4px}
h2{font-size:13px;color:#374151;margin:20px 0 8px;padding-bottom:5px;border-bottom:2px solid #06b6d4}
.sub{color:#6b7280;font-size:11px;margin-bottom:12px}
.info{background:#f0fdfe;padding:8px 12px;border-radius:6px;margin-bottom:15px;font-size:11px;color:#0891b2}
.stats{display:flex;gap:15px;margin-bottom:15px}
.stat{background:#f8fafc;border:1px solid #e5e7eb;border-radius:6px;padding:8px 15px;text-align:center}
.stat-n{font-size:18px;font-weight:bold;color:#06b6d4}
.stat-l{font-size:10px;color:#6b7280}
table{width:100%;border-collapse:collapse;margin-bottom:5px}
th{background:#06b6d4;color:white;padding:7px 9px;text-align:left;font-size:10px}
td{padding:6px 9px;border-bottom:1px solid #f3f4f6;font-size:10px}
tr:nth-child(even){background:#f9fafb}
.badge{display:inline-block;padding:1px 7px;border-radius:9999px;font-size:9px;font-weight:600}
.d{background:#dcfce7;color:#166534}.r{background:#fef3c7;color:#92400e}
.v{background:#fee2e2;color:#991b1b}.l{background:#dbeafe;color:#1e40af}
.i{background:#f3f4f6;color:#6b7280}.a{background:#dcfce7;color:#166534}
.e{background:#fef3c7;color:#92400e}
.footer{margin-top:20px;text-align:center;font-size:9px;color:#9ca3af;border-top:1px solid #e5e7eb;padding-top:10px}
.no-print{position:fixed;top:15px;right:15px;display:flex;gap:8px;z-index:999}
.btn-p{background:#06b6d4;color:white;border:none;padding:8px 16px;border-radius:6px;cursor:pointer;font-weight:600}
.btn-b{background:#e5e7eb;color:#374151;border:none;padding:8px 16px;border-radius:6px;cursor:pointer}
@media print{.no-print{display:none!important}body{print-color-adjust:exact;-webkit-print-color-adjust:exact}}
</style>
</head>
<body>
<div class="no-print">
    <button class="btn-b" onclick="window.history.back()">Retour</button>
    <button class="btn-p" onclick="window.print()">Imprimer / PDF</button>
</div>

<h1>ImmoGo - Rapport Complet</h1>
<p class="sub">{{ $agence->nom_commercial }} · {{ $agence->ville }} · Généré le {{ now()->format('d/m/Y H:i') }}</p>
<div class="info">📍 {{ $agence->adresse_complete }} | 📧 {{ $agence->email }}@if($agence->telephone) | 📞 {{ $agence->telephone }}@endif</div>

<div class="stats">
    <div class="stat"><div class="stat-n">{{ $biens->count() }}</div><div class="stat-l">Total biens</div></div>
    <div class="stat"><div class="stat-n">{{ $biens->where('is_published',true)->count() }}</div><div class="stat-l">Publiés</div></div>
    <div class="stat"><div class="stat-n">{{ $biens->where('statut','disponible')->count() }}</div><div class="stat-l">Disponibles</div></div>
    <div class="stat"><div class="stat-n">{{ $contrats->count() }}</div><div class="stat-l">Contrats actifs</div></div>
</div>

<h2>Liste des Biens ({{ $biens->count() }})</h2>
<table>
    <thead>
        <tr><th>#</th><th>Titre</th><th>Type</th><th>Localisation</th><th>Contrat</th><th>Prix FCFA</th><th>Superficie</th><th>Ch.</th><th>Statut</th><th>Publié</th></tr>
    </thead>
    <tbody>
        @forelse($biens as $i => $bien)
        <tr>
            <td>{{ $i+1 }}</td>
            <td><strong>{{ $bien->titre }}</strong></td>
            <td>{{ $bien->typeBien->libelle ?? 'N/A' }}</td>
            <td>{{ $bien->localisation }}, {{ $bien->ville }}</td>
            <td>{{ ucfirst($bien->transaction) }}</td>
            <td>{{ number_format($bien->prix, 0, ',', ' ') }}</td>
            <td>{{ $bien->superficie ? $bien->superficie.' m2' : '-' }}</td>
            <td>{{ $bien->chambres ?? '-' }}</td>
            <td>
                @php $cls=['disponible'=>'d','reserve'=>'r','vendu'=>'v','loue'=>'l','indisponible'=>'i'][$bien->statut]??'i'; @endphp
                <span class="badge {{ $cls }}">{{ ucfirst($bien->statut) }}</span>
            </td>
            <td>{{ $bien->is_published ? 'Oui' : 'Non' }}</td>
        </tr>
        @empty
        <tr><td colspan="10" style="text-align:center;padding:12px;color:#9ca3af">Aucun bien.</td></tr>
        @endforelse
    </tbody>
</table>

<h2>Clients & Contrats en cours ({{ $contrats->count() }})</h2>
<table>
    <thead>
        <tr>
            <th>#</th><th>Client</th><th>Email</th><th>Téléphone</th>
            <th>Bien</th><th>Type bien</th><th>Localisation</th>
            <th>Prix FCFA</th><th>Superficie</th><th>Contrat</th>
            <th>Payé FCFA</th><th>Solde FCFA</th><th>Statut</th><th>Date</th>
        </tr>
    </thead>
    <tbody>
        @forelse($contrats as $i => $contrat)
        <tr>
            <td>{{ $i+1 }}</td>
            <td><strong>{{ $contrat->client->prenom }} {{ $contrat->client->name }}</strong></td>
            <td>{{ $contrat->client->email }}</td>
            <td>{{ $contrat->client->telephone ?? '-' }}</td>
            <td>{{ $contrat->bien->titre }}</td>
            <td>{{ $contrat->bien->typeBien->libelle ?? 'N/A' }}</td>
            <td>{{ $contrat->bien->localisation }}, {{ $contrat->bien->ville }}</td>
            <td>{{ number_format($contrat->getMontantTotal(), 0, ',', ' ') }}</td>
            <td>{{ $contrat->bien->superficie ? $contrat->bien->superficie.' m2' : '-' }}</td>
            <td>{{ ucfirst($contrat->type_contrat) }}</td>
            <td>{{ number_format($contrat->getMontantPaye(), 0, ',', ' ') }}</td>
            <td>{{ number_format($contrat->getSoldeRestant(), 0, ',', ' ') }}</td>
            <td>
                @php $cs=['en_attente'=>'e','actif'=>'a'][$contrat->statut_contrat]??'i'; @endphp
                <span class="badge {{ $cs }}">{{ ucfirst(str_replace('_',' ',$contrat->statut_contrat)) }}</span>
            </td>
            <td>{{ $contrat->date_contrat->format('d/m/Y') }}</td>
        </tr>
        @empty
        <tr><td colspan="14" style="text-align:center;padding:12px;color:#9ca3af">Aucun contrat en cours.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">ImmoGo · Plateforme immobilière · Bénin · {{ now()->year }}</div>
</body>
</html>