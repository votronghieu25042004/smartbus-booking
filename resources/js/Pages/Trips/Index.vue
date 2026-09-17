<template>
  <div class="min-h-screen flex flex-col bg-slate-900 text-slate-100">
    <Navbar />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex-1 w-full">
      <!-- Search Filter Bar -->
      <div class="bg-slate-800/90 border border-slate-700 p-6 rounded-3xl shadow-xl mb-8">
        <form @submit.prevent="applyFilters" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <div>
            <label class="text-xs font-bold text-slate-300 uppercase">Điểm đi</label>
            <select v-model="filterForm.from_province_id" class="w-full mt-1 bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white">
              <option value="">Tất cả điểm đi</option>
              <option v-for="p in provinces" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>
          <div>
            <label class="text-xs font-bold text-slate-300 uppercase">Điểm đến</label>
            <select v-model="filterForm.to_province_id" class="w-full mt-1 bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white">
              <option value="">Tất cả điểm đến</option>
              <option v-for="p in provinces" :key="p.id" :value="p.id">{{ p.name }}</option>
            </select>
          </div>
          <div>
            <label class="text-xs font-bold text-slate-300 uppercase">Ngày đi</label>
            <input v-model="filterForm.date" type="date" class="w-full mt-1 bg-slate-900 border border-slate-700 rounded-xl px-3.5 py-2.5 text-white" />
          </div>
          <div class="flex items-end">
            <button type="submit" class="w-full py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 font-bold text-white transition-colors">
              Lọc kết quả
            </button>
          </div>
        </form>
      </div>

      <!-- Results Count & Sorting -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
          <h1 class="text-2xl font-black text-white">Danh sách chuyến xe</h1>
          <p class="text-slate-400 text-sm">Tìm thấy <span class="text-orange-400 font-bold">{{ trips.length }} chuyến</span> có sẵn chỗ</p>
        </div>
        <div class="flex items-center gap-3">
          <span class="text-xs text-slate-400 font-medium">Sắp xếp:</span>
          <select v-model="filterForm.sort_by" @change="applyFilters" class="bg-slate-800 border border-slate-700 rounded-xl px-3 py-1.5 text-xs text-white">
            <option value="departure_time_asc">Giờ khởi hành sớm nhất</option>
            <option value="price_asc">Giá vé thấp đến cao</option>
            <option value="price_desc">Giá vé cao đến thấp</option>
          </select>
        </div>
      </div>

      <!-- Trips List -->
      <div v-if="trips.length > 0" class="space-y-4">
        <div
          v-for="trip in trips"
          :key="trip.id"
          class="bg-slate-800/80 border border-slate-700/80 rounded-3xl p-6 hover:border-orange-500/50 hover:shadow-2xl transition-all"
        >
          <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-center">
            <!-- Left: Company & Vehicle -->
            <div class="lg:col-span-4 space-y-2">
              <div class="flex items-center gap-3">
                <img :src="trip.company_logo" :alt="trip.company_name" class="w-12 h-12 rounded-2xl object-cover border border-slate-700" />
                <div>
                  <h3 class="font-bold text-white text-base">{{ trip.company_name }}</h3>
                  <div class="flex items-center gap-2 text-xs">
                    <span class="text-amber-400 font-bold">★ {{ trip.rating }}</span>
                    <span class="text-slate-400">• Biển số: {{ trip.license_plate }}</span>
                  </div>
                </div>
              </div>
              <div class="inline-block px-3 py-1 rounded-full bg-slate-900 text-orange-400 text-xs font-semibold border border-orange-500/20">
                🚌 {{ trip.bus_type }}
              </div>
            </div>

            <!-- Middle: Departure & Arrival -->
            <div class="lg:col-span-5 flex items-center justify-between border-y lg:border-y-0 lg:border-x border-slate-700/60 py-4 lg:py-0 lg:px-6">
              <div class="text-left">
                <span class="text-2xl font-black text-white">{{ trip.departure_time }}</span>
                <span class="text-xs text-slate-400 block font-medium">{{ trip.departure_date }}</span>
                <span class="text-xs font-bold text-slate-300 block mt-1">📍 {{ trip.from_station }}</span>
              </div>

              <div class="flex flex-col items-center px-4">
                <span class="text-[11px] text-slate-400 mb-1">Trực tiếp</span>
                <div class="w-20 sm:w-28 h-0.5 bg-orange-500/40 relative">
                  <div class="w-2 h-2 rounded-full bg-orange-400 absolute -top-[3px] left-0"></div>
                  <div class="w-2 h-2 rounded-full bg-rose-400 absolute -top-[3px] right-0"></div>
                </div>
                <span class="text-[10px] text-green-400 font-bold mt-1">Còn {{ trip.available_seats }}/{{ trip.total_seats }} chỗ</span>
              </div>

              <div class="text-right">
                <span class="text-2xl font-black text-white">{{ trip.arrival_time }}</span>
                <span class="text-xs text-slate-400 block font-medium">{{ trip.arrival_date }}</span>
                <span class="text-xs font-bold text-slate-300 block mt-1">🏁 {{ trip.to_station }}</span>
              </div>
            </div>

            <!-- Right: Price & Booking Action -->
            <div class="lg:col-span-3 flex flex-col items-end justify-center space-y-3">
              <div class="text-right">
                <span class="text-xs text-slate-400 block">Tổng vé</span>
                <span class="text-2xl font-black text-amber-400">{{ formatPrice(trip.price) }}đ</span>
                <span class="text-xs font-bold text-green-400 block">Cọc 30%: {{ formatPrice(trip.deposit_30) }}đ</span>
              </div>

              <a
                :href="`/trips/${trip.id}`"
                class="w-full py-3 px-5 rounded-2xl bg-gradient-to-r from-orange-500 to-rose-500 hover:from-orange-600 hover:to-rose-600 text-white font-bold text-center text-sm shadow-lg shadow-orange-500/20 transition-all hover:scale-[1.02]"
              >
                Chọn Chỗ Ngồi & Đặt
              </a>
            </div>
          </div>
        </div>
      </div>

      <div v-else class="text-center py-20 bg-slate-800/40 border border-slate-700 rounded-3xl">
        <svg class="w-16 h-16 text-slate-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h3 class="text-xl font-bold text-white mb-2">Không tìm thấy chuyến xe phù hợp</h3>
        <p class="text-slate-400 text-sm mb-6">Vui lòng thử chọn ngày khác hoặc thử tuyến Đà Nẵng - Quảng Trị nhé!</p>
        <button @click="resetFilters" class="px-6 py-2.5 rounded-xl bg-orange-500 text-white font-bold text-sm">
          Xem tất cả chuyến xe
        </button>
      </div>
    </div>

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
  trips: Array,
  provinces: Array,
  filters: Object,
});

const filterForm = reactive({
  from_province_id: props.filters.from_province_id || '',
  to_province_id: props.filters.to_province_id || '',
  date: props.filters.date || '',
  sort_by: props.filters.sort_by || 'departure_time_asc',
});

const formatPrice = (val) => new Intl.NumberFormat('vi-VN').format(val);

const applyFilters = () => {
  router.get('/trips', filterForm, { preserveState: true });
};

const resetFilters = () => {
  filterForm.from_province_id = '';
  filterForm.to_province_id = '';
  applyFilters();
};
</script>
