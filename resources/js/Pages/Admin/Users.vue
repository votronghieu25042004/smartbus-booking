<template>
  <div class="min-h-screen flex flex-col bg-slate-950 text-slate-100">
    <AdminHeader title="QUẢN LÝ LƠ XE & NHÂN SỰ" subtitle="Danh sách 10 Lơ xe đón khách & Thu tiền tận tay" />

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-1 w-full space-y-8">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-black text-white">Danh sách Tài khoản (Lơ xe & Khách hàng)</h1>
          <p class="text-slate-400 text-xs mt-1">Cấp tài khoản nhân viên soát vé hoặc quản lý khách hàng đã đăng ký</p>
        </div>
        <button
          @click="showStaffModal = true"
          class="px-4 py-2.5 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-xs flex items-center gap-2 cursor-pointer shadow-lg shadow-emerald-500/20"
        >
          + Cấp Tài Khoản Lơ Xe Mới
        </button>
      </div>

      <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 overflow-hidden">
        <div class="overflow-x-auto custom-scrollbar">
          <table class="w-full text-left text-xs">
            <thead class="text-slate-400 uppercase border-b border-slate-800">
              <tr>
                <th class="pb-3">Họ và tên</th>
                <th class="pb-3">Email</th>
                <th class="pb-3">Số điện thoại</th>
                <th class="pb-3">Vai trò</th>
                <th class="pb-3">Số vé đã đặt</th>
                <th class="pb-3 text-right">Ngày tham gia</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/60 text-slate-300">
              <tr v-for="u in users" :key="u.id" class="hover:bg-slate-800/40">
                <td class="py-3.5 font-bold text-white text-sm">{{ u.name }}</td>
                <td class="py-3.5 font-mono text-slate-300">{{ u.email }}</td>
                <td class="py-3.5 text-slate-300">{{ u.phone || 'Chưa cập nhật' }}</td>
                <td class="py-3.5">
                  <span
                    :class="{
                      'bg-amber-500/20 text-amber-400 border-amber-500/30': u.role === 'admin',
                      'bg-emerald-500/20 text-emerald-400 border-emerald-500/30': u.role === 'staff',
                      'bg-blue-500/20 text-blue-400 border-blue-500/30': u.role === 'customer',
                    }"
                    class="px-2.5 py-1 rounded-full text-[11px] font-bold border"
                  >
                    {{ u.role === 'admin' ? '👑 Quản trị' : (u.role === 'staff' ? '🎫 Lơ xe / Soát vé' : '🧑 Khách hàng') }}
                  </span>
                </td>
                <td class="py-3.5 font-bold text-white">{{ u.total_bookings }} vé</td>
                <td class="py-3.5 text-right text-slate-500">{{ u.created_at }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Add Staff Modal -->
      <div v-if="showStaffModal" class="fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div class="bg-slate-900 border border-slate-800 max-w-md w-full rounded-3xl p-6 sm:p-8 space-y-4">
          <div class="flex justify-between items-center pb-3 border-b border-slate-800">
            <h3 class="text-lg font-black text-white">Cấp Tài Khoản Lơ Xe / Nhân Viên</h3>
            <button @click="showStaffModal = false" class="text-slate-400 hover:text-white">✕</button>
          </div>

          <form @submit.prevent="submitStaff" class="space-y-4">
            <div>
              <label class="text-xs font-bold text-slate-300 uppercase">Họ và tên nhân viên</label>
              <input v-model="staffForm.name" type="text" required placeholder="Ví dụ: Nguyễn Văn Lơ Xe" class="w-full mt-1 bg-slate-950 border border-slate-700 rounded-xl p-3 text-white text-sm" />
            </div>
            <div>
              <label class="text-xs font-bold text-slate-300 uppercase">Email</label>
              <input v-model="staffForm.email" type="email" required placeholder="staff2@futa.vn" class="w-full mt-1 bg-slate-950 border border-slate-700 rounded-xl p-3 text-white text-sm" />
            </div>
            <div>
              <label class="text-xs font-bold text-slate-300 uppercase">Số điện thoại</label>
              <input v-model="staffForm.phone" type="tel" required placeholder="0905 888 999" class="w-full mt-1 bg-slate-950 border border-slate-700 rounded-xl p-3 text-white text-sm" />
            </div>
            <div>
              <label class="text-xs font-bold text-slate-300 uppercase">Mật khẩu cấp</label>
              <input v-model="staffForm.password" type="password" required placeholder="Mật khẩu ban đầu" class="w-full mt-1 bg-slate-950 border border-slate-700 rounded-xl p-3 text-white text-sm" />
            </div>

            <button type="submit" class="w-full py-3.5 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold text-sm">
              TẠO TÀI KHOẢN LƠ XE
            </button>
          </form>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import AdminHeader from '@/Components/AdminHeader.vue'
import { ref, reactive } from 'vue';
import { router } from '@inertiajs/vue3';

defineProps({
  users: Array,
});

const showStaffModal = ref(false);

const staffForm = reactive({
  name: '',
  email: '',
  phone: '',
  password: '123456',
});

const submitStaff = () => {
  router.post('/admin/users/staff', staffForm, {
    onSuccess: () => {
      showStaffModal.value = false;
      staffForm.name = '';
      staffForm.email = '';
      staffForm.phone = '';
    }
  });
};
</script>
