<template>
  <div>
    <!-- Floating Trigger Button -->
    <button
      @click="isOpen = !isOpen"
      class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-5 py-3.5 rounded-full bg-gradient-to-r from-orange-500 via-rose-500 to-amber-500 text-white font-bold shadow-2xl shadow-orange-500/40 hover:scale-105 transition-all duration-300 cursor-pointer group"
    >
      <div class="relative">
        <svg class="w-6 h-6 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
        </svg>
        <span class="absolute -top-1 -right-1 w-2.5 h-2.5 bg-green-400 rounded-full border-2 border-slate-900"></span>
      </div>
      <span class="text-sm">Trợ lý AI Tìm Xe</span>
    </button>

    <!-- Chat Modal Window -->
    <div
      v-if="isOpen"
      class="fixed bottom-24 right-4 sm:right-6 w-[92vw] sm:w-[420px] max-h-[580px] h-[580px] bg-slate-900 border border-slate-700/80 rounded-3xl shadow-2xl z-50 flex flex-col overflow-hidden backdrop-blur-xl animate-fade-in"
    >
      <!-- Chat Header -->
      <div class="bg-gradient-to-r from-orange-600 to-rose-600 p-4 text-white flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center">
            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
          </div>
          <div>
            <h3 class="font-bold text-base leading-tight">SmartBus AI Assistant</h3>
            <p class="text-xs text-orange-100">Tìm chuyến xe thông minh 24/7</p>
          </div>
        </div>
        <button @click="isOpen = false" class="text-white/80 hover:text-white p-1.5 rounded-lg hover:bg-white/10 transition-colors">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Chat Messages Container -->
      <div ref="messageContainer" class="flex-1 overflow-y-auto p-4 space-y-4 custom-scrollbar bg-slate-950/60">
        <div
          v-for="(msg, idx) in messages"
          :key="idx"
          :class="msg.role === 'user' ? 'flex justify-end' : 'flex justify-start'"
        >
          <div
            :class="[
              'max-w-[85%] rounded-2xl p-3.5 text-sm leading-relaxed',
              msg.role === 'user'
                ? 'bg-orange-600 text-white rounded-tr-sm'
                : 'bg-slate-800 text-slate-200 border border-slate-700/60 rounded-tl-sm'
            ]"
          >
            <p class="whitespace-pre-line">{{ msg.text }}</p>

            <!-- Render Trip Cards if AI returns matching trips -->
            <div v-if="msg.trips && msg.trips.length > 0" class="mt-3 space-y-2.5">
              <div
                v-for="trip in msg.trips"
                :key="trip.id"
                class="bg-slate-900/90 border border-slate-700 rounded-xl p-3 text-xs text-slate-300 hover:border-orange-500/60 transition-all"
              >
                <div class="flex justify-between items-start font-bold text-white text-sm mb-1">
                  <span class="text-orange-400">{{ trip.company_name }}</span>
                  <span class="text-amber-400">{{ formatPrice(trip.price) }}đ</span>
                </div>
                <div class="text-slate-400 text-xs mb-1">
                  🕒 Xuất bến: <span class="text-white font-semibold">{{ trip.departure_time }} ({{ trip.departure_date }})</span>
                </div>
                <div class="text-slate-400 text-xs mb-2">
                  📍 {{ trip.from }} ➔ {{ trip.to }}
                </div>
                <div class="flex items-center justify-between pt-1 border-t border-slate-800">
                  <span class="text-[11px] text-green-400 font-medium">Cọc 30%: {{ formatPrice(trip.deposit_30) }}đ</span>
                  <a
                    :href="`/trips/${trip.id}`"
                    class="px-2.5 py-1 rounded-lg bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs inline-block transition-colors"
                  >
                    Chọn Ghế
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="loading" class="flex justify-start">
          <div class="bg-slate-800 border border-slate-700 rounded-2xl p-3 text-xs text-slate-400 flex items-center gap-2">
            <span class="w-2 h-2 bg-orange-400 rounded-full animate-bounce"></span>
            <span class="w-2 h-2 bg-rose-400 rounded-full animate-bounce [animation-delay:0.2s]"></span>
            <span class="w-2 h-2 bg-amber-400 rounded-full animate-bounce [animation-delay:0.4s]"></span>
            <span>AI đang tìm chuyến xe phù hợp...</span>
          </div>
        </div>
      </div>

      <!-- Quick Suggestion Buttons -->
      <div class="px-3 py-2 bg-slate-900 border-t border-slate-800 flex gap-2 overflow-x-auto custom-scrollbar">
        <button
          v-for="(sug, idx) in suggestions"
          :key="idx"
          @click="sendMessage(sug)"
          class="text-[11px] whitespace-nowrap px-3 py-1 rounded-full bg-slate-800 hover:bg-orange-500/20 text-slate-300 hover:text-orange-300 border border-slate-700 transition-colors"
        >
          {{ sug }}
        </button>
      </div>

      <!-- Input Bar -->
      <form @submit.prevent="sendMessage(inputMessage)" class="p-3 bg-slate-900 border-t border-slate-800 flex gap-2">
        <input
          v-model="inputMessage"
          type="text"
          placeholder="Nhắn điểm đi, điểm đến, giờ..."
          class="flex-1 bg-slate-950 border border-slate-700 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-orange-500"
        />
        <button
          type="submit"
          :disabled="loading || !inputMessage.trim()"
          class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 disabled:opacity-50 text-white font-bold text-sm transition-colors flex items-center justify-center cursor-pointer"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
          </svg>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, nextTick } from 'vue';
import axios from 'axios';

const isOpen = ref(false);
const inputMessage = ref('');
const loading = ref(false);
const messageContainer = ref(null);

const suggestions = [
  'Đà Nẵng về Quảng Trị tối mai',
  'Xe từ Huế đi Đông Hà',
  'Có xe giường nằm nào đi Hà Nội không?',
];

const messages = ref([
  {
    role: 'ai',
    text: '👋 Xin chào bạn! Tôi là Trợ lý ảo AI của SmartBus.\nBạn chỉ cần nhắn điểm đi, điểm đến hoặc câu hỏi bất kỳ (Ví dụ: "Tìm xe Đà Nẵng về Quảng Trị tối nay"), tôi sẽ gợi ý ngay!',
    trips: [],
  }
]);

const formatPrice = (val) => {
  return new Intl.NumberFormat('vi-VN').format(val);
};

const scrollToBottom = () => {
  nextTick(() => {
    if (messageContainer.value) {
      messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
    }
  });
};

const sendMessage = async (text) => {
  if (!text || !text.trim()) return;
  const userText = text.trim();
  inputMessage.value = '';

  messages.value.push({ role: 'user', text: userText });
  scrollToBottom();
  loading.value = true;

  try {
    const res = await axios.post('/ai/chat', { message: userText });
    messages.value.push({
      role: 'ai',
      text: res.data.reply,
      trips: res.data.trips || [],
    });
  } catch (err) {
    messages.value.push({
      role: 'ai',
      text: 'Xin lỗi, hệ thống AI tạm thời đang bận. Bạn có thể tìm trực tiếp trên thanh tìm kiếm nhé!',
      trips: [],
    });
  } finally {
    loading.value = false;
    scrollToBottom();
  }
};
</script>
