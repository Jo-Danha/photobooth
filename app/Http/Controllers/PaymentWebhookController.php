<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PhotoboothSession;
use Illuminate\Support\Facades\Log;

class PaymentWebhookController extends Controller
{
    public function handleMidtransNotification(Request $request)
    {
        $payload = $request->all();
        Log::info('Midtrans Webhook Received:', $payload);

        $orderId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;

        if (!$orderId) {
            return response()->json(['message' => 'Invalid order ID'], 400);
        }

        $session = PhotoboothSession::where('order_id', $orderId)->first();

        if (!$session) {
            return response()->json(['message' => 'Session not found'], 404);
        }

        if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
            if ($fraudStatus == 'accept' || empty($fraudStatus)) {
                $session->startSession();
            }
        } else if (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            $session->update(['payment_status' => 'failed']);
        }

        return response()->json(['status' => 'success']);
    }
}