<template>
  <div class="min-h-screen flex flex-col bg-slate-900 text-slate-100">
    <Navbar />

    <!-- Hero Section with Live Search -->
    <section class="relative pt-12 pb-24 px-4 sm:px-6 lg:px-8 overflow-hidden">
      <!-- Background Glow -->
      <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-gradient-to-tr from-orange-500/20 via-rose-500/10 to-transparent rounded-full blur-3xl pointer-events-none"></div>

      <div class="max-w-6xl mx-auto relative z-10">
        <!-- Hero Header -->
        <div class="text-center space-y-4 mb-12">
          <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-orange-500/10 border border-orange-500/30 text-orange-400 text-xs font-bold tracking-wide uppercase">
            <span class="w-2 h-2 rounded-full bg-orange-400 animate-ping"></span>
            Mạng lưới bến xe toàn quốc - Vé điện tử QR Code
          </div>
          <h1 class="text-4xl sm:text-6xl font-black tracking-tight text-white leading-tight">
            Đặt vé xe khách <span class="bg-gradient-to-r from-amber-400 via-orange-400 to-rose-500 bg-clip-text text-transparent">Toàn Quốc</span><br>
            Cọc 30% - Soát vé mã QR
          </h1>
          <p class="text-slate-400 max-w-2xl mx-auto text-base sm:text-lg">
            Kết nối hơn 100+ bến xe và các hãng xe hàng đầu Việt Nam. Chỉ cần cọc trước 30%, nhận vé QR qua Email và quét camera điện thoại check-in tại bến.
          </p>
        </div>

        <!-- Search Card -->
        <div class="bg-slate-800/80 backdrop-blur-xl border border-slate-700/80 p-6 sm:p-8 rounded-3xl shadow-2xl">
          <form @submit.prevent="handleSearch" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Điểm đi (Tỉnh / Bến đi) -->
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                </svg>
                Điểm đi (Nơi xuất phát)
              </label>
              <select
                v-model="form.from_province_id"
                class="w-full bg-slate-900 border border-slate-700 rounded-2xl px-4 py-3.5 text-white focus:outline-none focus:border-orange-500 font-medium"
              >
                <option value="">-- Chọn Tỉnh / Thành phố đi --</option>
                <option v-for="prov in provinces" :key="prov.id" :value="prov.id">
                  {{ prov.name }}
                </option>
              </select>
            </div>

            <!-- Điểm đến -->
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9" />
                </svg>
                Điểm đến (Nơi cần đến)
              </label>
              <select
                v-model="form.to_province_id"
                class="w-full bg-slate-900 border border-slate-700 rounded-2xl px-4 py-3.5 text-white focus:outline-none focus:border-orange-500 font-medium"
              >
                <option value="">-- Chọn Tỉnh / Thành phố đến --</option>
                <option v-for="prov in provinces" :key="prov.id" :value="prov.id">
                  {{ prov.name }}
                </option>
              </select>
            </div>

            <!-- Ngày đi -->
            <div class="space-y-1.5">
              <label class="text-xs font-bold text-slate-300 uppercase tracking-wider flex items-center gap-2">
                <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Ngày khởi hành
              </label>
              <input
                v-model="form.date"
                type="date"
                class="w-full bg-slate-900 border border-slate-700 rounded-2xl px-4 py-3.5 text-white focus:outline-none focus:border-orange-500 font-medium"
              />
            </div>

            <!-- Nút Tìm Chuyến -->
            <div class="flex items-end">
              <button
                type="submit"
                class="w-full py-3.5 px-6 rounded-2xl bg-gradient-to-r from-orange-500 to-rose-500 hover:from-orange-600 hover:to-rose-600 text-white font-black tracking-wide shadow-lg shadow-orange-500/30 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 cursor-pointer flex items-center justify-center gap-2 text-base"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                TÌM CHUYẾN XE
              </button>
            </div>
          </form>

          <!-- Quick Quick Filters -->
          <div class="mt-6 pt-5 border-t border-slate-700/60 flex flex-wrap items-center gap-3 text-xs text-slate-400">
            <span class="font-bold text-slate-300">Tuyến hot hôm nay:</span>
            <button
              @click="setQuickRoute(1, 2)"
              class="px-3 py-1 rounded-full bg-slate-900 hover:bg-orange-500/20 text-orange-400 border border-orange-500/30 transition-colors"
            >
              Đà Nẵng ➔ Quảng Trị (Đông Hà)
            </button>
            <button
              @click="setQuickRoute(3, 2)"
              class="px-3 py-1 rounded-full bg-slate-900 hover:bg-orange-500/20 text-orange-400 border border-orange-500/30 transition-colors"
            >
              Huế ➔ Quảng Trị (Đông Hà)
            </button>
            <button
              @click="setQuickRoute(1, 4)"
              class="px-3 py-1 rounded-full bg-slate-900 hover:bg-orange-500/20 text-orange-400 border border-orange-500/30 transition-colors"
            >
              Đà Nẵng ➔ Hà Nội
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- 4-Step Feature Cards -->
    <section class="py-16 bg-slate-950/60 border-y border-slate-800">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
          <h2 class="text-3xl font-black text-white">Quy trình Đặt vé & Soát vé QR Thông minh</h2>
          <p class="text-slate-400 text-sm mt-2">Đơn giản, tiện lợi, không lo mất chỗ, thanh toán cọc linh hoạt</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="bg-slate-900 border border-slate-800 p-6 rounded-3xl relative overflow-hidden group hover:border-orange-500/40 transition-colors">
            <div class="w-12 h-12 rounded-2xl bg-orange-500/10 text-orange-400 flex items-center justify-center font-black text-xl mb-4">
              01
            </div>
            <h3 class="text-lg font-bold text-white mb-2">Tìm chuyến & Chọn ghế</h3>
            <p class="text-slate-400 text-sm leading-relaxed">
              Chọn bến xe toàn quốc, xem loại xe limousine/giường nằm, tiện ích và chọn trực quan sơ đồ 2 tầng.
            </p>
          </div>

          <div class="bg-slate-900 border border-slate-800 p-6 rounded-3xl relative overflow-hidden group hover:border-orange-500/40 transition-colors">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center font-black text-xl mb-4">
              02
            </div>
            <h3 class="text-lg font-bold text-white mb-2">Thanh toán cọc 30%</h3>
            <p class="text-slate-400 text-sm leading-relaxed">
              Chỉ cần đặt cọc trước 30% để giữ chỗ. Hủy vé trước 24h được hoàn 100% tiền cọc minh bạch.
            </p>
          </div>

          <div class="bg-slate-900 border border-slate-800 p-6 rounded-3xl relative overflow-hidden group hover:border-orange-500/40 transition-colors">
            <div class="w-12 h-12 rounded-2xl bg-rose-500/10 text-rose-400 flex items-center justify-center font-black text-xl mb-4">
              03
            </div>
            <h3 class="text-lg font-bold text-white mb-2">Nhận vé QR Code</h3>
            <p class="text-slate-400 text-sm leading-relaxed">
              Mã QR vé điện tử được kích hoạt và gửi về Email khách hàng kèm thông tin quyền lợi và số tiền còn lại.
            </p>
          </div>

          <div class="bg-slate-900 border border-slate-800 p-6 rounded-3xl relative overflow-hidden group hover:border-orange-500/40 transition-colors">
            <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-black text-xl mb-4">
              04
            </div>
            <h3 class="text-lg font-bold text-white mb-2">Quét mã QR lên xe</h3>
            <p class="text-slate-400 text-sm leading-relaxed">
              Đến bến đưa mã QR, nhân viên dùng điện thoại quét kiểm tra, thu nốt 70% tiền vé và hoàn tất lên xe.
            </p>
          </div>
        </div>
      </div>
    </section>

    <!-- Popular Routes Section -->
    <section id="popular-routes" class="py-20 px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto w-full">
      <div class="flex justify-between items-end mb-10">
        <div>
          <h2 class="text-3xl font-black text-white">Tuyến đường phổ biến</h2>
          <p class="text-slate-400 text-sm mt-1">Các chặng xe khách liên tỉnh có nhiều chuyến chạy mỗi ngày</p>
        </div>
        <a href="/trips" class="text-orange-400 hover:text-orange-300 font-bold text-sm flex items-center gap-1">
          Xem tất cả <span aria-hidden="true">&rarr;</span>
        </a>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <div
          v-for="(route, idx) in popularRoutes"
          :key="idx"
          class="bg-slate-800/60 border border-slate-700/80 rounded-3xl overflow-hidden hover:border-orange-500/50 hover:shadow-xl transition-all group"
        >
          <div class="h-44 overflow-hidden relative">
            <img :src="route.image" :alt="route.to" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-transparent to-transparent"></div>
            <span class="absolute top-3 right-3 px-3 py-1 rounded-full bg-slate-900/80 backdrop-blur-md text-amber-400 text-xs font-bold border border-amber-500/30">
              ⏱ {{ route.duration }}
            </span>
          </div>
          <div class="p-5">
            <div class="text-xs text-slate-400 font-medium mb-1">{{ route.from }} ➔</div>
            <h3 class="text-lg font-bold text-white mb-3">{{ route.to }}</h3>
            <div class="flex items-center justify-between pt-3 border-t border-slate-700/60">
              <div>
                <span class="text-[11px] text-slate-400 block">Giá từ</span>
                <span class="text-base font-black text-orange-400">{{ route.price }}</span>
              </div>
              <a
                href="/trips"
                class="px-3.5 py-2 rounded-xl bg-orange-500/20 hover:bg-orange-500 text-orange-300 hover:text-white font-bold text-xs transition-colors"
              >
                Đặt vé
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Cancellation & Deposit Policy Notice -->
    <section id="cancellation-policy" class="py-16 bg-slate-950 border-t border-slate-800">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-orange-950/40 border border-orange-500/30 p-8 sm:p-10 rounded-3xl">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-orange-500/20 text-orange-400 flex items-center justify-center">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="text-2xl font-bold text-white">Chính sách Đặt cọc 30% & Hủy vé minh bạch</h3>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
            <div class="bg-slate-950/80 border border-slate-800 p-5 rounded-2xl">
              <span class="text-emerald-400 font-black text-lg block mb-1">Hoàn 100% Cọc</span>
              <p class="text-slate-300 font-semibold mb-2">Hủy trước 24 tiếng</p>
              <p class="text-slate-400 text-xs leading-relaxed">
                Khách hàng yêu cầu hủy trước giờ khởi hành trên 24h được hoàn trả 100% tiền đặt cọc tự động.
              </p>
            </div>

            <div class="bg-slate-950/80 border border-slate-800 p-5 rounded-2xl">
              <span class="text-amber-400 font-black text-lg block mb-1">Hoàn 50% Cọc</span>
              <p class="text-slate-300 font-semibold mb-2">Hủy từ 12h - 24 tiếng</p>
              <p class="text-slate-400 text-xs leading-relaxed">
                Hủy trong khoảng thời gian này khách hàng chịu 50% tiền cọc để hỗ trợ chi phí cho nhà xe.
              </p>
            </div>

            <div class="bg-slate-950/80 border border-slate-800 p-5 rounded-2xl">
              <span class="text-rose-400 font-black text-lg block mb-1">Mất 100% Cọc</span>
              <p class="text-slate-300 font-semibold mb-2">Hủy dưới 12 tiếng</p>
              <p class="text-slate-400 text-xs leading-relaxed">
                Quá sát giờ xe xuất bến, tiền cọc sẽ không được hoàn trả do không kịp bán lại cho khách khác.
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- AI Chatbot Floating Component -->
    <AiChatModal />

    <Footer />
  </div>
</template>

<script setup>
import { reactive } from 'vue';
import { router } from '@inertiajs/vue3';
import Navbar from '@/Components/Navbar.vue';
import Footer from '@/Components/Footer.vue';
import AiChatModal from '@/Components/AiChatModal.vue';

const props = defineProps({
  provinces: Array,
  popularRoutes: Array,
});

const today = new Date().toISOString().split('T')[0];

const form = reactive({
  from_province_id: 1, // Default Da Nang
  to_province_id: 2,   // Default Quang Tri
  date: today,
});

const handleSearch = () => {
  router.get('/trips', {
    from_province_id: form.from_province_id,
    to_province_id: form.to_province_id,
    date: form.date,
  });
};

const setQuickRoute = (fromId, toId) => {
  form.from_province_id = fromId;
  form.to_province_id = toId;
  handleSearch();
};
</script>
