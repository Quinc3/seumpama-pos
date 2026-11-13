<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Struk {{ $order->order_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            line-height: 1.4;
            width: 80mm;
            padding: 4mm;
            color: #000;
            background: #fff;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        .separator {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .header {
            margin-bottom: 8px;
        }

        .header .shop-name {
            font-size: 14px;
            font-weight: bold;
        }

        .header .shop-address {
            font-size: 10px;
            margin-top: 2px;
        }

        .meta {
            font-size: 10px;
            margin-bottom: 6px;
        }

        .meta-row {
            display: flex;
            justify-content: space-between;
        }

        .items {
            margin-bottom: 6px;
        }

        .item-name {
            margin-bottom: 2px;
        }

        .item-row {
            display: flex;
            justify-content: space-between;
            font-size: 10px;
            padding-left: 6px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            margin-top: 4px;
        }

        .grand-total {
            font-size: 13px;
            font-weight: bold;
        }

        .footer {
            font-size: 10px;
            margin-top: 10px;
            text-align: center;
        }

        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }

            body {
                padding: 2mm;
            }
        }
    </style>
</head>

<body>
    <div class="header center">
        <div class="shop-name">{{ $settings['shop_name'] }}</div>
        <div class="shop-address">{{ $settings['shop_address'] }}</div>
        @if ($settings['shop_phone'])
        <div class="shop-address">Telp: {{ $settings['shop_phone'] }}</div>
        @endif
    </div>

    <div class="separator"></div>

    <div class="meta">
        <div class="meta-row">
            <span>No. {{ $order->order_number }}</span>
            <span>{{ $order->created_at->format('d/m/y H:i') }}</span>
        </div>
        <div class="meta-row">
            <span>Kasir: {{ $order->user?->name }}</span>
            <span>{{ ucfirst($order->status) }}</span>
        </div>
    </div>

    <div class="separator"></div>

    <div class="items">
        @foreach ($order->items as $item)
        <div class="item-name">{{ $item->product_name }}</div>
        <div class="item-row">
            <span>{{ number_format((float) $item->quantity, 0) }} x Rp {{ number_format((float) $item->unit_price, 0, ',', '.') }}</span>
            <span>Rp {{ number_format((float) $item->subtotal, 0, ',', '.') }}</span>
        </div>
        @endforeach
    </div>

    <div class="separator"></div>

    <div>
        <div class="total-row">
            <span>Subtotal</span>
            <span>Rp {{ number_format((float) $order->subtotal, 0, ',', '.') }}</span>
        </div>
        @if ((float) $order->discount_amount > 0)
        <div class="total-row">
            <span>Diskon</span>
            <span>-Rp {{ number_format((float) $order->discount_amount, 0, ',', '.') }}</span>
        </div>
        @endif
        <div class="total-row grand-total" style="margin-top: 4px;">
            <span>TOTAL</span>
            <span>Rp {{ number_format((float) $order->total_amount, 0, ',', '.') }}</span>
        </div>
    </div>

    <div class="separator"></div>

    @php $payment = $order->payments->first(); @endphp
    @if ($payment)
    <div>
        <div class="total-row">
            <span>Bayar ({{ $payment->paymentMethod?->name }})</span>
            <span>Rp {{ number_format((float) $payment->amount, 0, ',', '.') }}</span>
        </div>
        @if ($payment->paymentMethod?->code === 'cash' && (float) $payment->amount > (float) $order->total_amount)
        <div class="total-row">
            <span>Kembali</span>
            <span>Rp {{ number_format((float) $payment->amount - (float) $order->total_amount, 0, ',', '.') }}</span>
        </div>
        @endif
        @if ($payment->reference_number)
        <div class="total-row" style="font-size: 10px;">
            <span>Ref</span>
            <span>{{ $payment->reference_number }}</span>
        </div>
        @endif
    </div>
    <div class="separator"></div>
    @endif

    @if ($order->notes)
    <div style="font-size: 10px; margin-bottom: 6px;">
        <em>Catatan: {{ $order->notes }}</em>
    </div>
    @endif

    <div class="footer">
        {{ $settings['receipt_footer'] }}<br>
        <span style="font-size: 9px;">Dicetak: {{ now()->format('d/m/Y H:i') }}</span>
    </div>
</body>

</html>
