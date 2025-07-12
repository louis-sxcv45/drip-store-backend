<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;

Route::post("/register", [AuthController::class, "register"]);
Route::post("/login", [AuthController::class, "login"]);
Route::middleware("auth:sanctum")->group(function () {
    Route::get("/profile", [AuthController::class, "show"]);
    Route::post("/update-user-profile", [
        AuthController::class,
        "updateUserProfile",
    ]);
    Route::post("/update-password", [AuthController::class, "updatePassword"]);
});

Route::middleware("auth:sanctum")->delete("/logout", function (Request $request) {
    $request->user()->currentAccessToken()->delete();
    return response()->json([
        "message" => "Logged out successfully",
    ]);
});

Route::get("/user", function (Request $request) {
    return $request->user();
})->middleware("auth:sanctum");

Route::get("/products", [ProductController::class, "index"]);
Route::get("/products/{id}", [ProductController::class, "show"]);

Route::middleware("auth:sanctum")
    ->prefix("/transaksi")
    ->group(function () {
        Route::post("/checkout", [TransactionController::class, "checkout"]);
        Route::get("/history", [TransactionController::class, "history"]);
    });

Route::post("/midtrans-webhook", [
    TransactionController::class,
    "webhook",
]);
