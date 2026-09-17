<template>
  <div class="min-h-screen flex flex-col bg-slate-950 text-slate-100">
    <Navbar :user="user" />

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-1 w-full">
      <!-- Success Badge -->
      <div class="text-center mb-8 space-y-2">
        <div class="w-16 h-16 bg-green-500/20 text-green-400 border border-green-500/40 rounded-full flex items-center justify-center mx-auto mb-3">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
          </svg>
        </div>
        <h1 class="text-3xl font-black text-white">Đặt vé Phương Trang & Cọc 30% Thành công!</h1>
        <p class="text-slate-400 text-sm">Mã QR vé điện tử đã được kích hoạt và gửi về email <span class="text-orange-400 font-bold">{{ booking.customer_email }}</span></p>
      </div>

      <!-- Electronic Ticket Boarding Pass -->
      <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-slate-950 border border-orange-500/30 rounded-3xl overflow-hidden shadow-2xl">
        <!-- Ticket Header Banner -->
        <div class="bg-gradient-to-r from-orange-500 via-amber-500 to-rose-500 p-6 text-white flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div>
            <span class="text-xs uppercase tracking-widest font-black opacity-90 block">VÉ ĐIỆN TỬ FUTA BUS LINES</span>
            <h2 class="text-2xl font-black">PHƯƠNG TRANG EXPRESS</h2>
          </div>
          <div class="text-left sm:text-right">
            <span class="text-xs opacity-90 block">Mã đặt chỗ (Booking Code)</span>
            <span class="text-2xl font-mono font-black tracking-wider bg-black/20 px-3 py-1 rounded-xl inline-block">{{ booking.booking_code }}</span>
          </div>
        </div>

        <div class="p-6 sm:p-8 grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
          <!-- QR Code Area -->
          <div class="md:col-span-4 flex flex-col items-center justify-center p-6 bg-white rounded-3xl shadow-inner text-slate-900 text-center">
            <qrcode-vue :value="booking.qr_token" :size="180" level="H" />
            <span class="text-xs font-mono font-bold mt-3 text-slate-800 tracking-wider">MÃ QR CHECK-IN LÊN XE</span>
            <span class="text-[10px] text-slate-500 mt-1">Đưa mã này cho Lơ xe quét khi đến bến</span>
          </div>

          <!-- Ticket Info Details -->
          <div class="md:col-span-8 space-y-4 text-sm">
            <div class="grid grid-cols-2 gap-4 pb-4 border-b border-slate-800">
              <div>
                <span class="text-xs text-slate-400 block">Hành khách:</span>
                <span class="font-bold text-white text-base">{{ booking.customer_name }}</span>
              </div>
              <div>
                <span class="text-xs text-slate-400 block">Số điện thoại:</span>
                <span class="font-bold text-white text-base">{{ booking.customer_phone }}</span>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4 pb-4 border-b border-slate-800">
              <div>
                <span class="text-xs text-slate-400 block">Điểm xuất phát:</span>
                <span class="font-bold text-orange-400 text-sm">📍 {{ booking.from_location }}</span>
                <span class="text-xs text-slate-400 block">{{ booking.departure_time }} ({{ booking.departure_date }})</span>
              </div>
              <div>
                <span class="text-xs text-slate-400 block">Điểm đến:</span>
                <span class="font-bold text-rose-400 text-sm">🏁 {{ booking.to_location }}</span>
                <span class="text-xs text-slate-400 block">{{ booking.arrival_time }}</span>
              </div>
            </div>

            <div class="grid grid-cols-3 gap-2 pb-4 border-b border-slate-800">
              <div>
                <span class="text-xs text-slate-400 block">Vị trí giường/ghế:</span>
                <span class="text-lg font-black text-amber-400">{{ booking.seats.join(', ') }}</span>
              </div>
              <div>
                <span class="text-xs text-slate-400 block">Loại xe:</span>
                <span class="text-xs font-bold text-slate-200">{{ booking.bus_type }}</span>
              </div>
              <div>
                <span class="text-xs text-slate-400 block">Tài xế phụ trách:</span>
                <span class="text-xs font-bold text-white">{{ booking.driver ? booking.driver.name : 'Đang phân công' }}</span>
              </div>
            </div>

            <!-- Financial Details -->
            <div class="bg-slate-950 p-4 rounded-2xl border border-slate-800 space-y-2">
              <div class="flex justify-between text-xs text-slate-300">
                <span>Tổng tiền vé:</span>
                <span class="font-bold text-white text-sm">{{ formatPrice(booking.total_amount) }}đ</span>
              </div>
              <div class="flex justify-between text-xs text-green-400 font-bold bg-green-500/10 p-2 rounded-xl">
                <span>✓ Đã thanh toán cọc Online (30%):</span>
                <span>{{ formatPrice(booking.deposit_amount) }}đ</span>
              </div>
              <div class="flex justify-between text-sm text-amber-400 font-black pt-1 border-t border-slate-800">
                <span>Cần thanh toán cho lơ xe khi lên xe (70%):</span>
                <span>{{ formatPrice(booking.remaining_amount) }}đ</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
        <!-- Review Driver Button -->
        <a
          :href="`/booking/${booking.booking_code}/review`"
          class="px-6 py-3.5 rounded-2xl bg-amber-500/20 hover:bg-amber-500 text-amber-300 hover:text-slate-950 border border-amber-500/40 font-bold text-sm transition-all"
        >
          ⭐ {{ booking.has_reviewed ? 'Xem / Cập nhật đánh giá Tài xế' : 'Đánh giá Tài xế sau chuyến đi' }}
        </a>
        <a
          href="/scanner"
          class="px-6 py-3.5 rounded-2xl bg-slate-800 hover:bg-slate-700 text-slate-300 border border-slate-700 font-bold text-sm transition-all"
        >
          📷 Thử quét QR vé này (Dành cho Lơ xe)
        </a>
        <a
          href="/trips"
          class="px-6 py-3.5 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm transition-all shadow-lg shadow-orange-500/20"
        >
          Đặt Thêm Vé Khác
        </a>
      </div>
    </div>
  </div>
</template>

<script setup>
import Navbar from '@/Components/Navbar.vue';
import QrcodeVue from 'qrcode.vue';

defineProps({
  booking: Object,
  user: Object,
});

const formatPrice = (val) => new Intl.NumberFormat('vi-VN').format(val || 0);
</script>
