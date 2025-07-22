<?php

namespace App\Http\Controllers;

use Midtrans\Snap;
use Midtrans\Config;
use App\Models\Product;
use Midtrans\Notification;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
        $validator = Validator::make(
            $request->all(),
            [
                "product_ids" => "required|array",
                "product_ids.*" => "exists:products,id",
            ],
            [
                "product_ids.required" => "Product IDs are required",
                "product_ids.array" => "Product IDs must be an array",
                "product_ids.*.exists" => "Product ID does not exist",
            ]
        );

        if ($validator->fails()) {
            return response()->json(["error" => $validator->errors()], 422);
        }

        // Calculate total amount
        $products = Product::whereIn('id', $request->product_ids)->get();
        $grouped = $products->groupBy('store_id');

        $totalAmount = 0;
        $userId = auth()->user()->id;
        $transactionIds = [];
        $firstTransactionId = null;

        foreach ($grouped as $storeId => $productsInStore) {
            $storeTotal = $productsInStore->sum('price');

            $transaction = Transaction::create([
                'user_id' => $userId,
                'store_id' => $storeId,
                'total_amount' => $storeTotal,
                'status' => 1,
            ]);

            if (!$firstTransactionId) {
                $firstTransactionId = $transaction->id;
            }

            $transactionIds[] = $transaction->id;

            foreach ($productsInStore as $product) {
                $product->decrement("quantity");

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'price' => $product->price,
                ]);
            }

            $totalAmount += $storeTotal;
        }

        $params = [
            "transaction_details" => [
                "order_id" => $firstTransactionId,
                "gross_amount" => $totalAmount,
            ],
            "customer_details" => [
                "first_name" => auth()->user()->name,
                "email" => auth()->user()->email,
                "phone" => auth()->user()->phone,
            ],
            "custom_field1" => implode(',', $transactionIds), // Simpan semua transaction IDs
        ];

        $snap = Snap::getSnapToken($params);

        return [
            "message" => "Transaction created successfully",
            "snap_token" => $snap,
        ];
    }

    public function webhook(Request $request)
    {
        $notification = (object) $request->all();

        $orderId = $notification->order_id;
        $transactionStatus = $notification->transaction_status;
        $fraudStatus = $notification->fraud_status;

        // Verifikasi signature key
        $signatureKey = hash('sha512', $orderId . $notification->status_code . $notification->gross_amount . Config::$serverKey);
        if ($signatureKey != $notification->signature_key) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // Cari transaction utama berdasarkan order_id
        $mainTransaction = Transaction::where('id', $orderId)->first();

        if (!$mainTransaction) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        // Cari semua transactions yang dibuat dalam waktu bersamaan (dalam 1 menit) oleh user yang sama
        $relatedTransactions = Transaction::where('user_id', $mainTransaction->user_id)
            ->whereBetween('created_at', [
                $mainTransaction->created_at->subMinute(),
                $mainTransaction->created_at->addMinute()
            ])
            ->where('status', 1) // Hanya yang masih pending
            ->get();

        // Update status semua related transactions
        foreach ($relatedTransactions as $transaction) {
            if ($transactionStatus === 'settlement') {
                $transaction->status = 2; // Set to 'settlement'
            } elseif ($transactionStatus === 'pending') {
                $transaction->status = 1; // Set to 'pending'
            } elseif ($transactionStatus === 'cancel' || $transactionStatus === 'deny') {
                $transaction->status = 3; // Set to 'cancelled'
            } elseif ($transactionStatus === 'expire') {
                $transaction->status = 4; // Set to 'expired'
            }

            $transaction->save();
        }

        return response()->json([
            'message' => 'Webhook processed successfully',
            'updated_transactions' => $relatedTransactions->count()
        ]);
    }

    public function history(Request $request)
    {
        $user = $request->user();

        $transactions = Transaction::with([
            'transactionItems' => function ($query) {
                $query->with([
                    'product' => function ($productQuery) {
                        $productQuery->join('stores', 'products.store_id', '=', 'stores.id')
                            ->select('products.*', 'stores.name_store', 'stores.logo');
                    }
                ]);
            }
        ])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($transaction) {
                foreach ($transaction->transactionItems as $item) {
                    if (isset($item->product->logo)) {
                        $logo = $item->product->logo;

                        // Hanya generate URL absolut jika belum dimulai dengan 'http'
                        if (!str_starts_with($logo, 'http')) {
                            $item->product->logo = url(Storage::url($logo));
                        }
                    }
                }
                return $transaction;
            })
            ->toArray();

        return response()->json([
            'message' => 'Transaction history fetched successfully',
            'data' => $transactions
        ]);
    }
}
