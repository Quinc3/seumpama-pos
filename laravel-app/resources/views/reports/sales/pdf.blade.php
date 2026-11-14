<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Sales Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica', Arial, sans-serif;
            font-size: 9px;
            color: #1e293b;
            padding: 12px;
            line-height: 1.3;
        }

        .header {
            text-align: center;
            border-bottom: 1.5px solid #1e293b;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 14px;
            margin-bottom: 2px;
            letter-spacing: 0.3px;
        }

        .header p {
            font-size: 9px;
            color: #64748b;
        }

        .meta {
            margin-bottom: 10px;
            font-size: 8px;
            color: #475569;
            text-align: center;
        }

        .kpi-grid {
            display: table;
            width: 100%;
            table-layout: fixed;
            margin-bottom: 12px;
            border-collapse: separate;
            border-spacing: 6px 0;
        }

        .kpi {
            display: table-cell;
            border: 1px solid #e2e8f0;
            border-radius: 3px;
            padding: 6px 8px;
            text-align: center;
            vertical-align: middle;
        }

        .kpi .label {
            font-size: 7px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 2px;
        }

        .kpi .value {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
            font-family: 'Courier New', monospace;
        }

        h2 {
            font-size: 10px;
            color: #0f172a;
            margin-top: 12px;
            margin-bottom: 4px;
            padding-bottom: 3px;
            border-bottom: 1px solid #e2e8f0;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5px;
        }

        th {
            background: #f8fafc;
            text-align: left;
            padding: 3px 6px;
            font-weight: 600;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
            font-size: 8px;
        }

        td {
            padding: 2.5px 6px;
            border-bottom: 1px solid #f1f5f9;
        }

        .text-right {
            text-align: right;
        }

        .mono {
            font-family: 'Courier New', monospace;
        }

        .footer {
            text-align: center;
            font-size: 7.5px;
            color: #94a3b8;
            margin-top: 14px;
            padding-top: 6px;
            border-top: 1px solid #e2e8f0;
        }

        .no-data {
            text-align: center;
            color: #94a3b8;
            font-size: 8px;
            padding: 6px 0;
            font-style: italic;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>SALES REPORT</h1>
        <p>Seumpama Bunga — Coffee Shop</p>
    </div>

    <div class="meta">
        Periode: {{ $from->format('d M Y') }} — {{ $to->format('d M Y') }}
        &nbsp;•&nbsp;
        Dibuat: {{ $generatedAt->format('d M Y H:i') }}
    </div>

    {{-- KPI Cards --}}
    <div class="kpi-grid">
        <div class="kpi">
            <div class="label">Total Penjualan</div>
            <div class="value">Rp {{ number_format($totalSales, 0, ',', '.') }}</div>
        </div>
        <div class="kpi">
            <div class="label">Transaksi</div>
            <div class="value">{{ number_format($totalTransactions) }}</div>
        </div>
        <div class="kpi">
            <div class="label">Rata-rata</div>
            <div class="value">Rp {{ number_format($avgTransaction, 0, ',', '.') }}</div>
        </div>
        <div class="kpi">
            <div class="label">Item Terjual</div>
            <div class="value">{{ (float) $totalItemsSold == floor((float) $totalItemsSold) ? number_format((float) $totalItemsSold, 0, ',', '.') : number_format((float) $totalItemsSold, 2, ',', '.') }}</div>
        </div>
    </div>

    {{-- Tren Harian --}}
    <h2>Tren Harian</h2>
    @if ($dailyData->isEmpty())
    <div class="no-data">Tidak ada data pada periode ini.</div>
    @else
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th class="text-right" style="width: 80px;">Transaksi</th>
                <th class="text-right" style="width: 100px;">Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($dailyData as $row)
            <tr>
                <td>{{ \Carbon\Carbon::parse($row->date)->format('d M Y') }}</td>
                <td class="text-right mono">{{ number_format($row->tx_count) }}</td>
                <td class="text-right mono">Rp {{ number_format($row->revenue, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Top Produk --}}
    <h2>Top 10 Produk</h2>
    @if ($topProducts->isEmpty())
    <div class="no-data">Tidak ada data.</div>
    @else
    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th class="text-right" style="width: 60px;">Qty</th>
                <th class="text-right" style="width: 100px;">Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($topProducts as $p)
            <tr>
                <td>{{ $p->product_name }}</td>
                <td class="text-right mono">{{ number_format($p->total_qty, 2) }}</td>
                <td class="text-right mono">Rp {{ number_format($p->total_revenue, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Per Kategori --}}
    <h2>Per Kategori</h2>
    @if ($byCategory->isEmpty())
    <div class="no-data">Tidak ada data.</div>
    @else
    <table>
        <thead>
            <tr>
                <th>Kategori</th>
                <th class="text-right" style="width: 60px;">Qty</th>
                <th class="text-right" style="width: 100px;">Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($byCategory as $cat)
            <tr>
                <td>{{ $cat->category_name ?? 'Tanpa Kategori' }}</td>
                <td class="text-right mono">{{ number_format($cat->total_qty, 2) }}</td>
                <td class="text-right mono">Rp {{ number_format($cat->total_revenue, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- Per Kasir --}}
    <h2>Performa Kasir</h2>
    @if ($byCashier->isEmpty())
    <div class="no-data">Tidak ada data.</div>
    @else
    <table>
        <thead>
            <tr>
                <th>Kasir</th>
                <th class="text-right" style="width: 70px;">Transaksi</th>
                <th class="text-right" style="width: 100px;">Revenue</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($byCashier as $c)
            <tr>
                <td>{{ $c->user_name }}</td>
                <td class="text-right mono">{{ number_format($c->tx_count) }}</td>
                <td class="text-right mono">Rp {{ number_format($c->total_revenue, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <div class="footer">
        Generated by Seumpama POS — {{ now()->format('d M Y H:i:s') }}
    </div>
</body>

</html>
