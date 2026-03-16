<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;

class InvoiceController extends Controller
{
    public function store(Request $request)
    {
        // 1. Datos de la factura
        $invoice = [
            "id" => rand(1000, 9999),
            "client_name" => $request->client_name,
            "total" => $request->total,
            "items" => $request->items
        ];

        $qrData = json_encode([
            "invoice" => $invoice["id"],
            "client" => $invoice["client_name"],
            "total" => $invoice["total"]
        ]);

        $qr = base64_encode(
            QrCode::format('svg')->size(150)->generate($qrData)
        );

        // 2. Generar PDF
        $pdf = Pdf::loadView('invoice', compact('invoice', 'qr'));
        $pdfContent = $pdf->output();
        $fileName = "invoice_" . $invoice["id"] . ".pdf";

        // 3. Guardado local (opcional)
        Storage::put("invoices/" . $fileName, $pdfContent);

        try {
            // 4. CONFIGURACIÓN DE GOOGLE DRIVE VIA OAUTH
            $client = new Client();
            $client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
            $client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
            $client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));

            $service = new Drive($client);

            // 5. Metadatos del archivo (Apuntando a tu carpeta)
            $fileMetadata = new DriveFile([
                'name' => $fileName,
                'parents' => [env('GOOGLE_DRIVE_FOLDER_ID')]
            ]);

            // 6. Subida del archivo
            $result = $service->files->create(
                $fileMetadata,
                [
                    'data' => $pdfContent,
                    'mimeType' => 'application/pdf',
                    'uploadType' => 'multipart',
                    'fields' => 'id'
                ]
            );

            $link = "https://drive.google.com/file/d/" . $result->id . "/view";

            return response()->json([
                "message" => "Factura generada y subida con éxito (Uso de cuota personal)",
                "file" => $fileName,
                "drive_link" => $link
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "error" => "Error al subir a Drive",
                "details" => $e->getMessage()
            ], 500);
        }
    }
}