<script setup>
import { ref, computed } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'

const props = defineProps({
    trip: Object,
    stops: Array,
    selectedPickup: Object,
    selectedDropoff: Object,
    floor1Rows: Array,
    floor2Rows: Array,
    occupiedSeats: Array,
    user: Object
})

const currentFloor = ref(1)
const selectedSeats = ref([])
const paymentChoice = ref('FULL') // 'FULL' (100%) hoặc 'DEPOSIT' (30%)

const pickupId = ref(props.selectedPickup?.id || (props.stops && props.stops[0]?.id))
const dropoffId = ref(props.selectedDropoff?.id || (props.stops && props.stops[props.stops.length - 1]?.id))

const changeSegment = () => {
    router.get(`/trips/${props.trip.id}`, {
        pickup_stop_id: pickupId.value,
        dropoff_stop_id: dropoffId.value
    }, {
        preserveState: false
    })
}

const toggleSeat = (seat) => {
    if (seat.status === 'booked') return
    const idx = selectedSeats.value.findIndex(s => s.seat_number === seat.seat_number)
    if (idx > -1) {
        selectedSeats.value.splice(idx, 1)
    } else {
        selectedSeats.value.push({
            seat_number: seat.seat_number,
            floor: seat.floor,
            price: seat.price,
            seat_type: seat.seat_type
        })
    }
}

const isSeatSelected = (seatNumber) => selectedSeats.value.some(s => s.seat_number === seatNumber)

const totalPrice = computed(() => {
    return selectedSeats.value.reduce((sum, s) => sum + (s.price || 0), 0)
})

const finalPayAmount = computed(() => {
    return paymentChoice.value === 'FULL' ? totalPrice.value : (totalPrice.value * 0.3)
})

const remainingAmount = computed(() => {
    return totalPrice.value - finalPayAmount.value
})

const form = useForm({
    customer_name: props.user?.name || '',
    customer_phone: props.user?.phone || '',
    customer_email: props.user?.email || '',
    pickup_stop_id: pickupId.value,
    dropoff_stop_id: dropoffId.value,
    seat_numbers: [],
    payment_choice: 'FULL',
    notes: ''
})

const submitBooking = () => {
    form.pickup_stop_id = pickupId.value
    form.dropoff_stop_id = dropoffId.value
    form.seat_numbers = selectedSeats.value.map(s => s.seat_number)
    form.payment_choice = paymentChoice.value
    form.post(`/trips/${props.trip.id}/book`)
}

const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0)
</script>

<template>
    <Head :title="'Đặt Vé Chuyến ' + trip.trip_code + ' - FUTA Bus Lines'" />

    <div class="min-h-screen bg-slate-50 text-slate-800 pb-20">
        <!-- Header -->
        <header class="bg-orange-600 text-white shadow-lg sticky top-0 z-30">
            <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between">
                <Link href="/" class="flex items-center space-x-2">
                    <span class="w-8 h-8 rounded-lg bg-white text-orange-600 font-black flex items-center justify-center text-lg shadow">F</span>
                    <span class="font-bold text-lg">FUTA BUS LINES</span>
                </Link>
                <div class="flex items-center space-x-3 text-sm">
                    <Link href="/trips" class="hover:underline font-semibold">« Quay lại danh sách chuyến</Link>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">
            <!-- Flash Message -->
            <div v-if="$page.props.flash?.error" class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-rose-700 text-sm font-semibold flex items-center space-x-2">
                <span>⚠️</span>
                <span>{{ $page.props.flash.error }}</span>
            </div>

            <!-- Segment Selector Box -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-200">
                <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-4">
                    <div>
                        <div class="flex items-center space-x-2">
                            <span class="text-xs font-bold text-orange-600 uppercase tracking-wider">Chọn Chặng Đường Đi (Seat Inventory by Segment)</span>
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 border border-emerald-300">
                                Xe {{ trip.license_plate }} ({{ trip.bus_type }})
                            </span>
                        </div>
                        <h1 class="text-xl font-black text-slate-900 mt-0.5">Chuyến {{ trip.trip_code }} — Xuất bến: {{ trip.departure_time }} ({{ trip.departure_date }})</h1>
                    </div>
                    <div class="text-right flex items-center space-x-4">
                        <div class="p-2.5 bg-orange-50 rounded-2xl border border-orange-200 text-left">
                            <div class="text-[11px] font-bold text-slate-500">Tầng 1 (Dưới):</div>
                            <div class="text-base font-black text-orange-600">{{ formatCurrency(trip.floor1_price) }}</div>
                        </div>
                        <div class="p-2.5 bg-indigo-50 rounded-2xl border border-indigo-200 text-left">
                            <div class="text-[11px] font-bold text-slate-500">Tầng 2 (Trên):</div>
                            <div class="text-base font-black text-indigo-600">{{ formatCurrency(trip.floor2_price) }}</div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 mt-4 items-end">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">📍 Điểm Đón (Lên xe)</label>
                        <select v-model="pickupId" @change="changeSegment" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-orange-500 focus:outline-none">
                            <option v-for="s in stops" :key="s.id" :value="s.id">
                                {{ s.stop_order }}. {{ s.stop_name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1.5">🏁 Điểm Trả (Xuống xe)</label>
                        <select v-model="dropoffId" @change="changeSegment" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-orange-500 focus:outline-none">
                            <option v-for="s in stops" :key="s.id" :value="s.id">
                                {{ s.stop_order }}. {{ s.stop_name }}
                            </option>
                        </select>
                    </div>

                    <div>
                        <button @click="changeSegment" class="w-full py-2.5 bg-orange-600 hover:bg-orange-500 text-white rounded-xl font-bold text-sm shadow-md transition flex items-center justify-center space-x-1.5">
                            <span>🔄</span>
                            <span>Cập Nhật Sơ Đồ Ghế & Giá Vé</span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Seat Map & Booking Form Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Left: Interactive 3-Column Seat Map (7 cols) -->
                <div class="lg:col-span-7 bg-white rounded-3xl p-6 shadow-sm border border-slate-200 space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <h2 class="text-base font-bold text-slate-900">Sơ Đồ Xe 3 Dãy Chuẩn FUTA</h2>
                            <p class="text-xs text-slate-400">Chặng: <strong class="text-orange-600">{{ selectedPickup?.stop_name }} ➔ {{ selectedDropoff?.stop_name }}</strong></p>
                        </div>
                        
                        <!-- Floor Switcher with Clear Labeling -->
                        <div class="flex p-1 bg-slate-100 rounded-2xl border border-slate-200">
                            <button 
                                @click="currentFloor = 1"
                                class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center space-x-1"
                                :class="currentFloor === 1 ? 'bg-orange-600 text-white shadow' : 'text-slate-600 hover:text-slate-900'">
                                <span>💺 Tầng Dưới (A)</span>
                                <span class="text-[10px] opacity-90">({{ formatCurrency(trip.floor1_price) }})</span>
                            </button>
                            <button 
                                @click="currentFloor = 2"
                                class="px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center space-x-1"
                                :class="currentFloor === 2 ? 'bg-indigo-600 text-white shadow' : 'text-slate-600 hover:text-slate-900'">
                                <span>🛏️ Tầng Trên (B)</span>
                                <span class="text-[10px] opacity-90">({{ formatCurrency(trip.floor2_price) }})</span>
                            </button>
                        </div>
                    </div>

                    <!-- Floor Info Banner -->
                    <div class="p-3 rounded-2xl text-xs font-semibold flex items-center justify-between"
                        :class="currentFloor === 1 ? 'bg-orange-50 border border-orange-200 text-orange-900' : 'bg-indigo-50 border border-indigo-200 text-indigo-900'">
                        <div>
                            <strong>{{ currentFloor === 1 ? 'TẦNG 1: ' + trip.floor1_type : 'TẦNG 2: ' + trip.floor2_type }}</strong>
                            <div class="text-[11px] opacity-80">{{ currentFloor === 1 ? 'Dãy giường/ghế êm ái, gần cửa lên xuống' : 'Dãy giường nằm phía trên thoáng đãng, ngắm cảnh' }}</div>
                        </div>
                        <div class="text-right font-bold text-sm">
                            {{ currentFloor === 1 ? formatCurrency(trip.floor1_price) : formatCurrency(trip.floor2_price) }} / vé
                        </div>
                    </div>

                    <!-- Seat Legend -->
                    <div class="flex items-center justify-center space-x-6 py-2 border-y border-slate-100 text-xs font-semibold text-slate-600">
                        <div class="flex items-center space-x-1.5">
                            <span class="w-4 h-4 rounded-lg bg-white border-2 border-slate-300"></span>
                            <span>Trống</span>
                        </div>
                        <div class="flex items-center space-x-1.5">
                            <span class="w-4 h-4 rounded-lg bg-orange-600 border-2 border-orange-600"></span>
                            <span>Đang chọn</span>
                        </div>
                        <div class="flex items-center space-x-1.5">
                            <span class="w-4 h-4 rounded-lg bg-slate-300 border-2 border-slate-400"></span>
                            <span>Đã có khách (Chặng này)</span>
                        </div>
                    </div>

                    <!-- Bus Cabin & 3-Column Grid -->
                    <div class="max-w-md mx-auto bg-slate-100/80 p-6 rounded-3xl border-2 border-slate-300 space-y-4 shadow-inner">
                        <!-- Cabin Front -->
                        <div class="flex items-center justify-between pb-3 border-b-2 border-dashed border-slate-300 text-xs font-black text-slate-600">
                            <div class="flex items-center space-x-1.5 px-3 py-1 bg-white rounded-lg border border-slate-200">
                                <span>🚪 CỬA LÊN</span>
                            </div>
                            <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">
                                {{ currentFloor === 1 ? 'TẦNG DƯỚI (A)' : 'TẦNG TRÊN (B)' }}
                            </div>
                            <div class="flex items-center space-x-1.5 px-3 py-1 bg-white rounded-lg border border-slate-200">
                                <span>🚗 TÀI XẾ</span>
                            </div>
                        </div>

                        <!-- 3 Columns Layout: Left Bed | Walkway 1 | Middle Bed | Walkway 2 | Right Bed -->
                        <div class="space-y-3">
                            <div 
                                v-for="(row, rIdx) in (currentFloor === 1 ? floor1Rows : floor2Rows)" 
                                :key="rIdx" 
                                class="grid grid-cols-5 gap-2 items-center">
                                <!-- Col 1: Left Bed (Dãy Trái / Cửa sổ trái) -->
                                <button 
                                    @click="toggleSeat(row.left)"
                                    :disabled="row.left.status === 'booked'"
                                    class="h-16 rounded-2xl flex flex-col items-center justify-center font-bold text-xs transition relative"
                                    :class="{
                                        'bg-slate-300 text-slate-400 cursor-not-allowed border border-slate-400': row.left.status === 'booked',
                                        'bg-orange-600 text-white shadow-lg scale-105 border-2 border-orange-700': isSeatSelected(row.left.seat_number),
                                        'bg-white text-slate-800 hover:border-orange-500 border border-slate-300 shadow-sm': row.left.status === 'available' && !isSeatSelected(row.left.seat_number)
                                    }">
                                    <span class="text-sm font-black">{{ row.left.seat_number }}</span>
                                    <span class="text-[9px] opacity-80">{{ formatCurrency(row.left.price) }}</span>
                                </button>

                                <!-- Col 2: Walkway 1 (Lối đi 1) -->
                                <div class="h-14 flex items-center justify-center text-[10px] text-slate-400 font-semibold tracking-tighter uppercase writing-mode-vertical">
                                    <span class="hidden sm:inline">Lối đi</span>
                                </div>

                                <!-- Col 3: Middle Bed (Dãy Giữa) -->
                                <button 
                                    @click="toggleSeat(row.middle)"
                                    :disabled="row.middle.status === 'booked'"
                                    class="h-16 rounded-2xl flex flex-col items-center justify-center font-bold text-xs transition relative"
                                    :class="{
                                        'bg-slate-300 text-slate-400 cursor-not-allowed border border-slate-400': row.middle.status === 'booked',
                                        'bg-orange-600 text-white shadow-lg scale-105 border-2 border-orange-700': isSeatSelected(row.middle.seat_number),
                                        'bg-white text-slate-800 hover:border-orange-500 border border-slate-300 shadow-sm': row.middle.status === 'available' && !isSeatSelected(row.middle.seat_number)
                                    }">
                                    <span class="text-sm font-black">{{ row.middle.seat_number }}</span>
                                    <span class="text-[9px] opacity-80">{{ formatCurrency(row.middle.price) }}</span>
                                </button>

                                <!-- Col 4: Walkway 2 (Lối đi 2) -->
                                <div class="h-14 flex items-center justify-center text-[10px] text-slate-400 font-semibold tracking-tighter uppercase writing-mode-vertical">
                                    <span class="hidden sm:inline">Lối đi</span>
                                </div>

                                <!-- Col 5: Right Bed (Dãy Phải / Cửa sổ phải) -->
                                <button 
                                    @click="toggleSeat(row.right)"
                                    :disabled="row.right.status === 'booked'"
                                    class="h-16 rounded-2xl flex flex-col items-center justify-center font-bold text-xs transition relative"
                                    :class="{
                                        'bg-slate-300 text-slate-400 cursor-not-allowed border border-slate-400': row.right.status === 'booked',
                                        'bg-orange-600 text-white shadow-lg scale-105 border-2 border-orange-700': isSeatSelected(row.right.seat_number),
                                        'bg-white text-slate-800 hover:border-orange-500 border border-slate-300 shadow-sm': row.right.status === 'available' && !isSeatSelected(row.right.seat_number)
                                    }">
                                    <span class="text-sm font-black">{{ row.right.seat_number }}</span>
                                    <span class="text-[9px] opacity-80">{{ formatCurrency(row.right.price) }}</span>
                                </button>
                            </div>
                        </div>

                        <!-- Bus Tail -->
                        <div class="pt-3 border-t-2 border-dashed border-slate-300 text-center text-[11px] font-bold text-slate-400">
                            DUÔI XE (CỬA THOÁT HIỂM)
                        </div>
                    </div>
                </div>

                <!-- Right: Booking Form & Payment Choice (5 cols) -->
                <div class="lg:col-span-5 bg-white rounded-3xl p-6 shadow-sm border border-slate-200 space-y-5 flex flex-col justify-between">
                    <div class="space-y-4">
                        <h2 class="text-base font-bold text-slate-900 pb-2 border-b border-slate-100">Thông Tin Hành Khách</h2>

                        <form @submit.prevent="submitBooking" class="space-y-3 text-xs">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Họ và tên hành khách</label>
                                <input v-model="form.customer_name" required type="text" placeholder="Nguyễn Văn A" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-orange-500 font-semibold" />
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Số điện thoại nhận vé & QR</label>
                                <input v-model="form.customer_phone" required type="text" placeholder="090xxxxxxx" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-orange-500 font-semibold" />
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Email (Nhận biên lai điện tử)</label>
                                <input v-model="form.customer_email" type="email" placeholder="email@gmail.com" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-orange-500 font-semibold" />
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Ghi chú đón trả</label>
                                <textarea v-model="form.notes" rows="2" placeholder="Gần ngã tư, mang vali to..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-orange-500"></textarea>
                            </div>
                        </form>
                    </div>

                    <!-- Payment Summary Box -->
                    <div class="bg-orange-50/70 border border-orange-200 rounded-2xl p-4 space-y-3">
                        <div class="flex justify-between text-xs text-slate-600">
                            <span>Ghế đã chọn:</span>
                            <strong class="text-orange-600 font-black text-sm">{{ selectedSeats.length > 0 ? selectedSeats.map(s => s.seat_number).join(', ') : 'Chưa chọn ghế' }}</strong>
                        </div>
                        <div class="flex justify-between text-xs text-slate-600">
                            <span>Tổng giá trị vé:</span>
                            <strong class="text-slate-900 text-sm font-black">{{ formatCurrency(totalPrice) }}</strong>
                        </div>

                        <!-- Payment Choice Radio Buttons -->
                        <div class="border-t border-orange-200 pt-3 space-y-2">
                            <label class="block text-xs font-bold text-slate-800">Hình thức thanh toán trực tuyến:</label>
                            
                            <label class="flex items-center justify-between p-2.5 bg-white rounded-xl border border-slate-200 cursor-pointer transition hover:border-orange-500">
                                <div class="flex items-center space-x-2">
                                    <input type="radio" value="FULL" v-model="paymentChoice" class="text-orange-600 focus:ring-orange-500" />
                                    <div>
                                        <div class="font-bold text-xs text-slate-800">Thanh toán đủ 100%</div>
                                        <div class="text-[10px] text-slate-500">Lên xe chỉ cần quét mã QR, không cần trả thêm</div>
                                    </div>
                                </div>
                                <span class="font-bold text-xs text-emerald-600">{{ formatCurrency(totalPrice) }}</span>
                            </label>

                            <label class="flex items-center justify-between p-2.5 bg-white rounded-xl border border-slate-200 cursor-pointer transition hover:border-orange-500">
                                <div class="flex items-center space-x-2">
                                    <input type="radio" value="DEPOSIT" v-model="paymentChoice" class="text-orange-600 focus:ring-orange-500" />
                                    <div>
                                        <div class="font-bold text-xs text-slate-800">Đặt cọc 30% giữ chỗ</div>
                                        <div class="text-[10px] text-slate-500">70% còn lại ({{ formatCurrency(remainingAmount) }}) trả cho Lơ xe khi lên xe</div>
                                    </div>
                                </div>
                                <span class="font-bold text-xs text-orange-600">{{ formatCurrency(totalPrice * 0.3) }}</span>
                            </label>
                        </div>

                        <div class="flex justify-between text-xs text-slate-800 border-t border-orange-200/60 pt-2 font-bold">
                            <span>Số tiền thanh toán ngay:</span>
                            <strong class="text-orange-600 text-lg font-black">{{ formatCurrency(finalPayAmount) }}</strong>
                        </div>

                        <button 
                            @click="submitBooking"
                            :disabled="selectedSeats.length === 0 || form.processing"
                            class="w-full py-3.5 bg-orange-600 hover:bg-orange-500 disabled:bg-slate-300 text-white font-black rounded-xl shadow-lg shadow-orange-600/30 transition text-sm">
                            {{ form.processing ? 'Đang xuất vé...' : 'XÁC NHẬN THANH TOÁN & LẤY MÃ QR' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
