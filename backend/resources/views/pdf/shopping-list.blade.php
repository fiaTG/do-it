<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #333; font-size: 12px; }
        h1 { color: #406f8f; font-size: 20px; margin-bottom: 2px; }
        .meta { color: #888; margin-bottom: 16px; }
        h2 { color: #406f8f; font-size: 14px; margin: 14px 0 4px; border-bottom: 1px solid #ddd; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 4px 0; }
        .qty { width: 60px; color: #888; }
        .empty { color: #888; }
    </style>
</head>
<body>
    <h1>Einkaufsliste</h1>
    <div class="meta">
        Familie {{ $family?->name ?? '' }} &middot; {{ now()->format('d.m.Y') }}
    </div>

    @php($grouped = $items->groupBy(fn ($item) => $item->shop?->name ?? 'Ohne Shop'))

    @forelse ($grouped as $shopName => $shopItems)
        <h2>{{ $shopName }}</h2>
        <table>
            @foreach ($shopItems as $item)
                <tr>
                    <td class="qty">{{ $item->quantity }}&times;</td>
                    <td>{{ $item->name }}</td>
                </tr>
            @endforeach
        </table>
    @empty
        <p class="empty">Die Einkaufsliste ist leer.</p>
    @endforelse
</body>
</html>
