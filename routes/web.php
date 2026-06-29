<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\UMKMController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GoogleMapsController;
use App\Http\Controllers\AIConsultationController;
use App\Http\Controllers\UserAIChatController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderRatingController;
use App\Http\Controllers\OrderChatController;
use App\Http\Controllers\WithdrawController;
use App\Http\Controllers\UMKMFinanceController;

// Public routes
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
    Route::post('/upload-pdf', [AdminController::class, 'uploadPdf'])->name('upload.pdf');
    Route::get('/umkm/create', [AdminController::class, 'createUmkm'])->name('umkm.create');
    Route::post('/umkm/store', [AdminController::class, 'storeUmkm'])->name('umkm.store');
    Route::get('/umkm/{id}/edit', [AdminController::class, 'editUmkm'])->name('umkm.edit');
    Route::put('/umkm/{id}/update', [AdminController::class, 'updateUmkm'])->name('umkm.update');
    Route::delete('/umkm/{id}/delete', [AdminController::class, 'deleteUmkm'])->name('umkm.delete');
    
    // Laporan routes
    Route::get('/laporan', [AdminController::class, 'laporan'])->name('laporan');
    Route::get('/laporan/{id}/detail', [AdminController::class, 'getLaporan'])->name('laporan.show');
    Route::put('/laporan/{id}/status', [AdminController::class, 'updateStatusLaporan'])->name('laporan.update.status');
    Route::delete('/laporan/{id}', [AdminController::class, 'deleteLaporan'])->name('laporan.delete');
    Route::post('/laporan/{id}/deduct', [AdminController::class, 'deductUmkm'])->name('laporan.deduct');
    Route::get('/laporan/pending-count', [AdminController::class, 'pendingLaporanCount'])->name('laporan.pending-count');
    
    // Tracking transaksi user-umkm
    Route::get('/transactions', [AdminController::class, 'allTransactions'])->name('transactions');
    Route::get('/transactions/{id}/detail', [AdminController::class, 'getTransactionDetail'])->name('transactions.detail');
    Route::post('/transactions/{id}/refund', [AdminController::class, 'refundTransaction'])->name('transactions.refund');

    // User management routes
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{id}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{id}/update', [AdminController::class, 'updateUser'])->name('users.update');

    // Withdraw / WD management
    Route::get('/withdraws', [AdminController::class, 'withdrawRequests'])->name('withdraws');
    Route::get('/withdraws/pending-count', [AdminController::class, 'withdrawPendingCount'])->name('withdraws.pending-count');
    Route::post('/withdraws/{id}/process', [AdminController::class, 'processWithdraw'])->name('withdraws.process');
    Route::post('/withdraws/mark-seen', [AdminController::class, 'markWithdrawSeen'])->name('withdraws.mark-seen');
    Route::post('/laporan/{id}/refund', [AdminController::class, 'processRefundUmkm'])->name('laporan.refund');
});

// UMKM routes
Route::middleware(['auth', 'role:umkm'])->prefix('umkm')->name('umkm.')->group(function () {
    Route::get('/dashboard', [UMKMController::class, 'dashboard'])->name('dashboard');
    
    // Keuangan routes (mutasi & summary)
    Route::get('/finance/summary', [UMKMFinanceController::class, 'financeSummary'])->name('finance.summary');
    Route::get('/finance/history', [UMKMFinanceController::class, 'financeHistory'])->name('finance.history');
    Route::get('/finance/orders', [UMKMFinanceController::class, 'orderHistory'])->name('finance.orders');
    Route::post('/profile/update', [UMKMController::class, 'updateProfile'])->name('profile.update');
    Route::get('/account/edit', [UMKMController::class, 'editAccount'])->name('edit.account');
    Route::put('/account/update', [UMKMController::class, 'updateAccount'])->name('update.account');
    Route::post('/keuntungan/store', [UMKMController::class, 'storeKeuntungan'])->name('keuntungan.store');
    Route::delete('/keuntungan/{id}/delete', [UMKMController::class, 'deleteKeuntungan'])->name('keuntungan.delete');
    Route::post('/excel/upload', [UMKMController::class, 'uploadExcel'])->name('excel.upload');
    Route::get('/excel/template', [UMKMController::class, 'downloadKeuntunganTemplate'])->name('excel.template');
    Route::post('/layanan/update', [UMKMController::class, 'updateLayanan'])->name('layanan.update');
    Route::delete('/layanan/{id}/remove', [UMKMController::class, 'removeLayanan'])->name('layanan.remove');
    Route::get('/keuntungan/data', [UMKMController::class, 'getKeuntunganData'])->name('keuntungan.data');
    
    // AI Consultation routes
    Route::get('/ai-consultation', [AIConsultationController::class, 'index'])->name('ai-consultation');
    Route::post('/ai-consultation/chat', [AIConsultationController::class, 'chat'])->name('ai-consultation.chat');
    Route::get('/ai-consultation/tips', [AIConsultationController::class, 'getBusinessTips'])->name('ai-consultation.tips');

    Route::get('/keuntungan/{id}', [UMKMController::class, 'getKeuntungan'])->name('keuntungan.get');
    Route::put('/keuntungan/{id}/update', [UMKMController::class, 'updateKeuntungan'])->name('keuntungan.update');
    Route::get('/layanan/{id}', [UMKMController::class, 'getLayanan'])->name('layanan.get');
    Route::post('/layanan/{id}/update', [UMKMController::class, 'updateLayananEdit'])->name('layanan.update.edit');
    
    // History Laporan
    Route::get('/history-laporan', [UMKMController::class, 'historyLaporan'])->name('history.laporan');
    
    // Komentar
    Route::get('/komentar', [UMKMController::class, 'komentar'])->name('komentar');

    // Video
    Route::get('/videos/create', [VideoController::class, 'create'])->name('videos.create');
    Route::post('/videos', [VideoController::class, 'store'])->name('videos.store');

    // Saldo Online & Withdraw
    Route::get('/saldo', [WithdrawController::class, 'getSaldo'])->name('saldo');
    Route::get('/withdraws', [WithdrawController::class, 'myWithdraws'])->name('withdraws');
    Route::post('/withdraws/request', [WithdrawController::class, 'requestWithdraw'])->name('withdraws.request');
    Route::get('/withdraws/unread-count', [WithdrawController::class, 'unreadCount'])->name('withdraws.unread');
});

// User routes
Route::middleware(['auth', 'role:user'])->prefix('user')->name('user.')->group(function () {
    Route::get('/katalog', [UserController::class, 'index'])->name('katalog');
    Route::get('/umkm/{id}', [UserController::class, 'show'])->name('umkm.show');
    Route::post('/favorite/{id}/toggle', [UserController::class, 'toggleFavorite'])->name('favorite.toggle');
    Route::get('/recommendations', [UserController::class, 'getRecommendations'])->name('recommendations');
    Route::get('/distance/{umkmId}/{userLat}/{userLng}', [UserController::class, 'getDistance'])->name('distance');

    // AI Chat for users
    Route::get('/ai-chat', [UserAIChatController::class, 'chatPage'])->name('ai.chat');
    Route::post('/ai-chat/send', [UserAIChatController::class, 'send'])->name('ai.chat.send');
    
    // History Laporan
    Route::get('/history-laporan', [UserController::class, 'historyLaporan'])->name('history.laporan');
    
    // Profile management
    Route::get('/profile/edit', [UserController::class, 'editProfile'])->name('edit.profile');
    Route::put('/profile/update', [UserController::class, 'updateProfile'])->name('update.profile');
    Route::get('/account', [UserController::class, 'account'])->name('account');
    
    // Follow
    Route::post('/umkm/{umkm}/follow', [FollowController::class, 'toggle'])->name('follow.toggle');

    // Pesanan History
    Route::get('/orders', [OrderController::class, 'userOrdersPage'])->name('orders');
    Route::get('/orders/{id}', [OrderController::class, 'userOrderDetailPage'])->name('orders.detail');
});

// Public UMKM catalog (for non-authenticated users)
Route::get('/katalog', [UserController::class, 'index'])->name('public.katalog');
Route::get('/umkm/{id}', [UserController::class, 'show'])->name('public.umkm.show');
Route::get('/layanan/{id}', [UserController::class, 'showLayanan'])->name('public.layanan.show');
Route::get('/laporan', [UserController::class, 'laporan'])->name('public.laporan');
Route::post('/laporan', [UserController::class, 'submitLaporan'])->name('public.laporan.submit');

// Public Video routes
Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');
Route::get('/videos/{video}', [VideoController::class, 'show'])->name('videos.show');
Route::get('/videos/umkm/{id}', [App\Http\Controllers\UMKMVideoProfileController::class, 'show'])->name('videos.umkm.profile');

// Video social features (require auth)
Route::middleware('auth')->group(function () {
    Route::post('/videos/{video}/like', [App\Http\Controllers\VideoLikeController::class, 'toggle'])->name('videos.like');
    Route::post('/videos/{video}/comment', [App\Http\Controllers\VideoCommentController::class, 'store'])->name('videos.comment');
    Route::get('/videos/{video}/comments', [App\Http\Controllers\VideoCommentController::class, 'index'])->name('videos.comments');
});

// Video view tracking (public)
Route::post('/videos/{video}/view', [VideoController::class, 'incrementView'])->name('videos.view');

// Comment routes (require authentication)
Route::middleware('auth')->group(function () {
    // UMKM comments
    Route::post('/umkm/{umkmId}/comment', [UserController::class, 'storeComment'])->name('comment.store');
    Route::put('/comment/{commentId}', [UserController::class, 'updateComment'])->name('comment.update');
    Route::delete('/comment/{commentId}', [UserController::class, 'deleteComment'])->name('comment.delete');
    
    // Layanan comments
    Route::post('/layanan/{layananId}/comment', [UserController::class, 'storeCommentLayanan'])->name('comment.layanan.store');
    Route::put('/layanan/comment/{commentId}', [UserController::class, 'updateCommentLayanan'])->name('comment.layanan.update');
    Route::delete('/layanan/comment/{commentId}', [UserController::class, 'deleteCommentLayanan'])->name('comment.layanan.delete');
});

// Order & Payment routes
Route::get('/api/orders/pricing', [OrderController::class, 'pricingConfig'])->name('orders.pricing');
Route::post('/api/orders', [OrderController::class, 'store'])->middleware('auth')->name('orders.store');
Route::post('/api/orders/notification', [OrderController::class, 'notification'])->name('orders.notification');
Route::get('/orders/payment/finish', [OrderController::class, 'paymentFinish'])->name('orders.payment.finish');
Route::get('/orders/payment/error', [OrderController::class, 'paymentError'])->name('orders.payment.error');
Route::get('/orders/payment/pending', [OrderController::class, 'paymentPending'])->name('orders.payment.pending');

// User: API order history & detail
Route::middleware('auth')->group(function () {
    Route::get('/api/user/orders', [OrderController::class, 'userHistory'])->name('api.user.orders');
    Route::get('/api/user/orders/{id}', [OrderController::class, 'userOrderDetail'])->name('api.user.orders.detail');

    // Rating
    Route::get('/api/orders/{id}/rating', [OrderRatingController::class, 'show'])->name('api.orders.rating.show');
    Route::post('/api/orders/{id}/rating', [OrderRatingController::class, 'store'])->name('api.orders.rating.store');

    // Chat
    Route::get('/api/orders/{id}/chat', [OrderChatController::class, 'index'])->name('api.orders.chat.index');
    Route::post('/api/orders/{id}/chat', [OrderChatController::class, 'store'])->name('api.orders.chat.store');
    Route::post('/api/orders/{id}/chat/read', [OrderChatController::class, 'markRead'])->name('api.orders.chat.read');

    // Confirm Payment
    Route::post('/api/orders/{id}/confirm-payment', [OrderController::class, 'confirmPayment'])->name('api.orders.confirm-payment');

    // Cancel Order
    Route::post('/api/orders/{id}/cancel', [OrderController::class, 'userCancel'])->name('api.orders.cancel');
});

Route::middleware(['auth', 'role:umkm'])->prefix('api/umkm')->group(function () {
    Route::get('/orders/pending', [OrderController::class, 'pendingForUmkm'])->name('umkm.orders.pending');
    Route::get('/orders', [OrderController::class, 'indexForUmkm'])->name('umkm.orders.index');
    Route::post('/orders/mark-seen', [OrderController::class, 'markSeen'])->name('umkm.orders.mark-seen');
    Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus'])->name('umkm.orders.update-status');
    Route::put('/orders/{id}/driver', [OrderController::class, 'updateDriverInfo'])->name('umkm.orders.driver');
});

// Ruang Diskusi Laporan & Mediasi
Route::middleware('auth')->group(function () {
    Route::get('/laporan/{id}/discussion', [UserController::class, 'showDiscussionPage'])->name('laporan.discussion');
    Route::post('/laporan/{id}/discussion', [UserController::class, 'postDiscussionMessage'])->name('laporan.discussion.send');
});

// Google Maps API routes
Route::post('/api/geocode', [GoogleMapsController::class, 'geocode'])->name('api.geocode');
Route::post('/api/reverse-geocode', [GoogleMapsController::class, 'reverseGeocode'])->name('api.reverse-geocode');
Route::get('/api/nearby-places', [GoogleMapsController::class, 'getNearbyPlaces'])->name('api.nearby-places');

// Fallback route - untuk semua route yang tidak ditemukan (404)
Route::fallback(function () {
    abort(404);
});
