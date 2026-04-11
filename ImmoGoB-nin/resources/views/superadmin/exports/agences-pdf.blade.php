<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Liste des Agences - ImmoGo</title>
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:Arial,sans-serif; font-size:12px; color:#1f2937; }
.header { background:#06b6d4; color:white; padding:20px 30px; display:flex; justify-content:space-between; align-items:center; }
.header h1 { font-size:20px; font-weight:bold; }
.header p { font-size:11px; opacity:0.9; margin-top:4px; }
table { width:100%; border-collapse:collapse; }
thead { background:#f8fafc; }
th { padding:10px 12px; text-align:left; font-size:10px; font-weight:600; color:#6b7280; text-transform:uppercase; border-bottom:2px solid #e5e7eb; }
td { padding:9px 12px; border-bottom:1px solid #f3f4f6; font-size:11px; }
tr:nth-child(even) { background:#f9fafb; }
.badge { display:inline-block; padding:2px 8px; border-radius:9999px; font-size:10px; font-weight:500; }
.actif { background:#dcfce7; color:#166534; }
.en_attente { background:#fef3c7; color:#92400e; }
.suspendu { background:#fee2e2; color:#991b1b; }
.footer { padding:15px 30px; text-align:center; font-size:10px; color:#9ca3af; border-top:1px solid #e5e7eb; margin-top:20px; }
.no-print { position:fixed; top:20px; right:20px; display:flex; gap:10px; z-index:999; }
.btn-p { background:#06b6d4; color:white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-size:13px; font-weight:600; }
.btn-b { background:#f3f4f6; color:#374151; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-size:13px; }
@media print { .no-print { display:none !important; } body { print-color-adjust:exact; -webkit-print-color-adjust:exact; } }
</style>
</head>
<body>

<div class="no-print">
    <button class="btn-b" onclick="window.history.back()">← Retour</button>
    <button class="btn-p" onclick="window.print()">🖨️ Imprimer / PDF</button>
</div>

<div class="header">
    <div>
        <h1>ImmoGo — Liste des Agences</h1>
        <p>Plateforme immobilière · Bénin</p>
    </div>
    <div style="text-align:right;">
        <p style="font-size:14px;font-weight:bold;">{{ $agences->count() }} agences</p>
        <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>#</th><th>Nom Commercial</th><th>Secteur</th><th>Ville</th>
            <th>Email</th><th>Admin Principal</th><th>Biens</th><th>Statut</th>
        </tr>
    </thead>
    <tbody>
        @forelse($agences as $i => $agence)
        <tr>
            <td>{{ $i+1 }}</td>
            <td><strong>{{ $agence->nom_commercial }}</strong></td>
            <td>{{ $agence->secteur }}</td>
            <td>{{ $agence->ville }}</td>
            <td>{{ $agence->email }}</td>
            <td>
                @if($agence->adminPrincipal)
                    {{ $agence->adminPrincipal->prenom }} {{ $agence->adminPrincipal->name }}
                @else
                    <span style="color:#9ca3af;">—</span>
                @endif
            </td>
            <td>{{ $agence->biens_count }}</td>
            <td><span class="badge {{ $agence->statut }}">{{ ucfirst(str_replace('_',' ',$agence->statut)) }}</span></td>
        </tr>
        @empty
        <tr><td colspan="8" style="text-align:center;padding:20px;color:#9ca3af;">Aucune agence.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="footer">ImmoGo — Plateforme immobilière · Bénin · {{ now()->year }}</div>
</body>
</html>
