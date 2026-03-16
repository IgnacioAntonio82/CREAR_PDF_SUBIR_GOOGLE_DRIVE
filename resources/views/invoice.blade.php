<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura</title>

    <style>
        body{
            font-family: DejaVu Sans, sans-serif;
            margin:40px;
            color:#333;
        }

        .header{
            text-align:center;
            margin-bottom:30px;
        }

        .title{
            font-size:28px;
            font-weight:bold;
        }

        .invoice-info{
            margin-bottom:20px;
        }

        .invoice-info p{
            margin:4px 0;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-top:20px;
        }

        table th{
            background:#f2f2f2;
            border:1px solid #ddd;
            padding:8px;
        }

        table td{
            border:1px solid #ddd;
            padding:8px;
        }

        .total{
            text-align:right;
            margin-top:20px;
            font-size:18px;
            font-weight:bold;
        }

        .footer{
            margin-top:40px;
            text-align:center;
            font-size:12px;
            color:#777;
        }
    </style>
</head>

<body>

<div class="header">
    <div class="title">FACTURA</div>
</div>

<div class="invoice-info">
    <p><strong>Número:</strong> {{ $invoice['id'] }}</p>
    <p><strong>Cliente:</strong> {{ $invoice['client_name'] }}</p>
    <p><strong>Fecha:</strong> {{ date('d/m/Y') }}</p>
</div>

<table>
    <thead>
        <tr>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio</th>
            <th>Total</th>
        </tr>
    </thead>

    <tbody>

        @foreach($invoice['items'] as $item)
        <tr>
            <td>{{ $item['name'] }}</td>
            <td>{{ $item['qty'] }}</td>
            <td>${{ $item['price'] }}</td>
            <td>${{ $item['qty'] * $item['price'] }}</td>
        </tr>
        @endforeach

    </tbody>
</table>

<div class="total">
    Total: ${{ $invoice['total'] }}
</div>

<div style="margin-top:30px; text-align:center;">
    <p><strong>QR de verificación</strong></p>
    <img src="data:image/svg+xml;base64,{{ $qr }}" width="120">
</div>

<div class="footer">
    Gracias por su compra
</div>

</body>
</html>