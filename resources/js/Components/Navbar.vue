<template>
  <nav class="bg-slate-900/95 backdrop-blur-md border-b border-orange-500/20 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-20">
        <!-- Logo -->
        <a href="/" class="flex items-center gap-3 group">
          <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-orange-500 to-amber-500 flex items-center justify-center text-white shadow-lg shadow-orange-500/30 group-hover:scale-105 transition-transform">
            <span class="font-black text-xl tracking-tighter">FUTA</span>
          </div>
          <div>
            <div class="flex items-center gap-2">
              <span class="text-2xl font-black bg-gradient-to-r from-orange-400 via-amber-400 to-emerald-400 bg-clip-text text-transparent tracking-tight">PHƯƠNG TRANG</span>
              <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-orange-500/20 text-orange-400 border border-orange-500/30">FUTA BUS LINES</span>
            </div>
            <span class="text-xs block text-slate-400 font-medium">Chất lượng là danh dự • QR E-Ticket & AI</span>
          </div>
        </a>

        <!-- Desktop Navigation -->
        <div class="hidden md:flex items-center gap-6 text-sm">
          <a href="/" class="text-slate-300 hover:text-orange-400 font-semibold transition-colors">Trang chủ</a>
          <a href="/trips" class="text-slate-300 hover:text-orange-400 font-semibold transition-colors">Tìm chuyến FUTA</a>
          <a href="/#cancellation-policy" class="text-slate-300 hover:text-orange-400 font-semibold transition-colors">Chính sách cọc 30%</a>
          
          <!-- Role specific links -->
          <a v-if="user && user.role === 'admin'" href="/admin" class="text-amber-400 font-bold hover:text-amber-300 flex items-center gap-1">
            <span>👑 Quản trị Admin</span>
          </a>
          <a v-if="user && (user.role === 'staff' || user.role === 'admin')" href="/scanner" class="text-emerald-400 font-bold hover:text-emerald-300 flex items-center gap-1">
            <span>📷 Soát vé QR (Lơ xe)</span>
          </a>
        </div>

        <!-- Auth & Actions -->
        <div class="flex items-center gap-3">
          <!-- User Logged In -->
          <div v-if="user" class="flex items-center gap-3">
            <div class="text-right hidden sm:block">
              <span class="text-xs font-bold text-white block">{{ user.name }}</span>
              <span class="text-[10px] uppercase font-bold px-2 py-0.5 rounded-md inline-block"
                :class="{
                  'bg-amber-500/20 text-amber-400 border border-amber-500/30': user.role === 'admin',
                  'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30': user.role === 'staff',
                  'bg-blue-500/20 text-blue-400 border border-blue-500/30': user.role === 'customer',
                }">
                {{ user.role === 'admin' ? 'Quản trị viên' : (user.role === 'staff' ? 'Lơ xe / Nhân viên' : 'Hành khách') }}
              </span>
            </div>
            <button
              @click="logout"
              class="px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-rose-500/20 text-slate-300 hover:text-rose-400 border border-slate-700 text-xs font-bold transition-colors cursor-pointer"
            >
              Đăng xuất
            </button>
          </div>

          <!-- Guest -->
          <div v-else class="flex items-center gap-2">
            <a
              href="/login"
              class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold transition-colors"
            >
              Đăng nhập
            </a>
            <a
              href="/register"
              class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-xs font-bold transition-colors shadow-lg shadow-orange-500/20"
            >
              Đăng ký
            </a>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { router } from '@inertiajs/vue3';

defineProps({
  user: Object,
});

const logout = () => {
  router.post('/logout');
};
</script>
