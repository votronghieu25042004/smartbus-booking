<template>
  <div class="min-h-screen flex flex-col bg-slate-950 text-slate-100 justify-center items-center px-4 py-12">
    <div class="w-full max-w-md space-y-6">
      <!-- Logo Header -->
      <div class="text-center space-y-2">
        <a href="/" class="inline-flex items-center gap-3">
          <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-orange-500 to-amber-500 flex items-center justify-center text-white shadow-xl shadow-orange-500/30">
            <span class="font-black text-xl">FUTA</span>
          </div>
        </a>
        <h2 class="text-2xl font-black text-white">ĐĂNG NHẬP HỆ THỐNG</h2>
        <p class="text-xs text-slate-400">Nhà xe Phương Trang (FUTA Bus Lines)</p>
      </div>

      <!-- Quick 1-Click Role Login Demo Buttons -->
      <div class="bg-slate-900 border border-slate-800 p-4 rounded-3xl space-y-2.5">
        <span class="text-[11px] font-bold text-slate-400 uppercase block text-center">⚡ Đăng nhập nhanh thử nghiệm (3 Roles)</span>
        <div class="grid grid-cols-3 gap-2">
          <button
            type="button"
            @click="quickLogin('admin@futa.vn', '123456')"
            class="p-2.5 rounded-2xl bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 text-amber-300 text-xs font-bold text-center transition-all cursor-pointer"
          >
            👑 Admin<br><span class="text-[9px] opacity-70 font-normal">Quản trị</span>
          </button>
          <button
            type="button"
            @click="quickLogin('staff@futa.vn', '123456')"
            class="p-2.5 rounded-2xl bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 text-emerald-300 text-xs font-bold text-center transition-all cursor-pointer"
          >
            🎫 Lơ Xe<br><span class="text-[9px] opacity-70 font-normal">Soát vé QR</span>
          </button>
          <button
            type="button"
            @click="quickLogin('hieu@gmail.com', '123456')"
            class="p-2.5 rounded-2xl bg-orange-500/10 hover:bg-orange-500/20 border border-orange-500/30 text-orange-300 text-xs font-bold text-center transition-all cursor-pointer"
          >
            🧑 Khách Hàng<br><span class="text-[9px] opacity-70 font-normal">Đặt vé</span>
          </button>
        </div>
      </div>

      <!-- Login Form -->
      <div class="bg-slate-900 border border-slate-800 p-6 sm:p-8 rounded-3xl shadow-2xl">
        <form @submit.prevent="submit" class="space-y-4">
          <div v-if="errors.email" class="p-3 bg-rose-500/10 border border-rose-500/30 rounded-xl text-xs text-rose-400 font-bold">
            {{ errors.email }}
          </div>

          <div>
            <label class="text-xs font-bold text-slate-300 uppercase">Email đăng nhập</label>
            <input
              v-model="form.email"
              type="email"
              required
              placeholder="admin@futa.vn hoặc email của bạn"
              class="w-full mt-1 bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-orange-500 text-sm"
            />
          </div>

          <div>
            <label class="text-xs font-bold text-slate-300 uppercase">Mật khẩu</label>
            <input
              v-model="form.password"
              type="password"
              required
              placeholder="Nhập mật khẩu"
              class="w-full mt-1 bg-slate-950 border border-slate-700 rounded-xl px-4 py-3 text-white focus:outline-none focus:border-orange-500 text-sm"
            />
          </div>

          <button
            type="submit"
            :disabled="form.processing"
            class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white font-black text-sm tracking-wide shadow-lg shadow-orange-500/30 transition-all cursor-pointer"
          >
            ĐĂNG NHẬP
          </button>

          <div class="text-center pt-2 text-xs text-slate-400">
            Chưa có tài khoản? <a href="/register" class="text-orange-400 font-bold hover:underline">Đăng ký ngay</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

defineProps({
  errors: Object,
});

const form = useForm({
  email: 'admin@futa.vn',
  password: '123456',
  remember: true,
});

const submit = () => {
  form.post('/login');
};

const quickLogin = (email, pwd) => {
  form.email = email;
  form.password = pwd;
  submit();
};
</script>
