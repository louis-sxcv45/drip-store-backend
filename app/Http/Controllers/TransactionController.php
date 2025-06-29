<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Validator;
use Midtrans\Notification;
use Midtrans\Snap;
use Midtrans\Config;

class TransactionController extends Controller
{
    public function __construct()
    {
        Config::$serverKey = config("services.midtrans.server_key");
        Config::$clientKey = config("services.midtrans.client_key");
        Config::$isProduction = config("services.midtrans.is_production");
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }
    public function checkout(Request $request)
    {
        // TODO: Create validation
        $validator = Validator::make(
            $request->all(),
            [
                "product_ids" => "required|array",
                "product_ids.*" => "exists:products,id",
                "store_id" => "required|exists:stores,id",
            ],
            [
                "product_ids.required" => "Product IDs are required",
                "product_ids.array" => "Product IDs must be an array",
                "product_ids.*.exists" => "Product ID does not exist",
                "store_id.required" => "Store ID is required",
                "store_id.exists" => "Store ID does not exist",
            ]
        );

        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors()], 422);
        }

        // Calculate total amount
        $totalAmount = 0;
        foreach ($request->product_ids as $productId) {
            $product = Product::find($productId);
            $totalAmount += $product->price;
        }

        // Create Transaksi
        $transaksi = Transaction::create([
            "user_id" => auth()->user()->id,
            "store_id" => $request->store_id,
            "total_amount" => $totalAmount,
            "status" => 1,
        ]);

        foreach ($request->product_ids as $productId) {
            $product = Product::find($productId);
            $product->decrement("quantity");

            // Create transaction items
            TransactionItem::create([
                "transaction_id" => $transaksi->id,
                "product_id" => $productId,
                "quantity" => 1,
                "price" => $product->price,
            ]);
        }

        $params = [
            "transaction_details" => [
                "order_id" => $transaksi->id,
                "gross_amount" => $totalAmount,
            ],
            "customer_details" => [
                "first_name" => auth()->user()->name,
                "email" => auth()->user()->email,
                "phone" => auth()->user()->phone,
            ],
        ];

        $snap = Snap::getSnapToken($params);

        return [
            "message" => "Transaction created successfully",
            "snap_token" => $snap,
        ];
    }

    public function webhook(Request $request) {
        $notification = new Notification();

        $orderId = $notification->order_id;
        $transactionStatus = $notification->transaction_status;
        $fraudStatus = $notification->fraud_status;

        dd($transactionStatus);

        // Verifikasi signature key
        $signatureKey = hash('sha512', $orderId . $notification->status_code . $notification->gross_amount . Config::$serverKey);
        if ($signatureKey != $notification->signature_key) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

         $transaction = Transaction::where('id', $orderId)->first();

        if ($transactionStatus === 'settlement') {
            $transaction->status = 2; // Set to 'settlement'
            $transaction->save();
        } elseif ($transactionStatus === 'pending') {
            $transaction->status = 1; // Set to 'pending'
            $transaction->save();
        } elseif ($transactionStatus === 'cancel' || $transactionStatus === 'deny') {
            $transaction->status = 3; // Set to 'cancelled'
            $transaction->save();
        } elseif ($transactionStatus === 'expire') {
            $transaction->status = 4; // Set to 'expired'
            $transaction->save();
        }

        return response()->json(['message' => 'Webhook processed successfully']);
    }
}
