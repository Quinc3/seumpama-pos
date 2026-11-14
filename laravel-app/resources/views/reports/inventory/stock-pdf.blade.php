<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Stock Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 7.5px;
            color: #1e293b;
            padding: 8px;
            line-height: 1.2;
        }

        .header {
            text-align: center;
            border-bottom: 1px solid #1e293b;
            padding-bottom: 3px;
            margin-bottom: 5px;
        }

        .header h1 {
            font-size: 11px;
            letter-spacing: 0.3px;
        }

        .header p {
            font-size: 7px;
            color: #64748b;
            margin-top: 1px;
        }

        .meta {
            text-align: center;
            font-size: 7px;
            color: #475569;
            margin-bottom: 6px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f1f5f9;
            text-align: left;
            padding: 2px 3px;
            font-weight: 600;
            color: #475569;
            border-bottom: 0.5px solid #cbd5e1;
            font-size: 7px;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }

        td {
            padding: 1.5px 3px;
            border-bottom: 0.5px solid #f1f5f9;
            font-size: 7.5px;
        }

        tr:nth-child(even) td {
            background: #fafbfc;
        }

        .text-right {
            text-align: right;
        }

        .mono {
            font-family: 'Courier New', monospace;
        }

        .status-normal {
            color: #16a34a;
        }

        .status-warning {
            color: #d97706;
            font-weight: 600;
        }

        .status-critical {
            color: #dc2626;
            font-weight: 700;
        }

        .status-out {
            color: #7f1d1d;
            font-weight: 700;
        }

        .footer {
            text-align: center;
            font-size: 6.5px;
            color: #94a3b8;
            margin-top: 8px;
            padding-top: 4px;
            border-top: 0.5px solid #e2e8f0;
        }

        .summary {
            display: table;
            width: 100%;
            margin-bottom: 6px;
            border-collapse: separate;
            border-spacing: 4px 0;
        }

        .summary-cell {
            display: table-cell;
            text-align: center;
            padding: 3px;
            border: 0.5px solid #e2e8f0;
            border-radius: 2px;
            font-size: 7px;
        }

        .summary-cell .num {
            font-size: 11px;
            font-weight: bold;
            font-family: 'Courier New', monospace;
        }

        .summary-cell .lbl {
            color: #64748b;
            text-transform: uppercase;
            font-size: 6.5px;
            letter-spacing: 0.2px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>STOCK OVERVIEW REPORT</h1>
        <p>Seumpama Bunga — Coffee Shop</p>
    </div>
    <div class="meta">Dibuat: {{ $generatedAt->format('d M Y H:i') }}</div>

    {{-- Summary --}}
    <div class="summary">
        <div class="summary-cell">
            <div class="lbl">Total</div>
            <div class="num">{{ $items->count() }}</div>
        </div>
        <div class="summary-cell">
            <div class="lbl">Normal</div>
            <div class="num" style="color: #16a34a;">{{ $items->where('stock_status', 'normal')->count() }}</div>
        </div>
        <div class="summary-cell">
            <div class="lbl">Menipis</div>
            <div class="num" style="color: #d97706;">{{ $items->where('stock_status', 'warning')->count() }}</div>
        </div>
        <div class="summary-cell">
            <div class="lbl">Kritis</div>
            <div class="num" style="color: #dc2626;">{{ $items->where('stock_status', 'critical')->count() }}</div>
        </div>
        <div class="summary-cell">
            <div class="lbl">Habis</div>
            <div class="num" style="color: #7f1d1d;">{{ $items->where('stock_status', 'out')->count() }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 55px;">SKU</th>
                <th>Nama</th>
                <th style="width: 60px;">Kategori</th>
                <th style="width: 40px;">Tipe</th>
                <th class="text-right" style="width: 85px;">Stok</th>
                <th class="text-right" style="width: 50px;">Min</th>
                <th class="text-right" style="width: 50px;">Safety</th>
                <th style="width: 55px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($items as $item)
            <tr>
                <td class="mono">{{ $item->sku }}</td>
                <td>{{ $item->name }}</td>
                <td>{{ $item->category ?: '—' }}</td>
                <td>{{ $item->ingredient_type === 'core' ? 'Core' : 'Non' }}</td>
                <td class="text-right mono">
                    {{ number_format((float) $item->current_stock, 2) }} {{ $item->unit_symbol }}
                </td>
                <td class="text-right mono">{{ $item->minimum_stock ? number_format((float) $item->minimum_stock, 2) : '—' }}</td>
                <td class="text-right mono">{{ $item->safety_stock ? number_format((float) $item->safety_stock, 2) : '—' }}</td>
                <td class="status-{{ $item->stock_status }}">{{ ucfirst($item->stock_status) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Generated by Seumpama POS — {{ now()->format('d M Y H:i:s') }}
    </div>
</body>

</html>
