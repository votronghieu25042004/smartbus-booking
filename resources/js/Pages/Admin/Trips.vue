<script setup>
import AdminHeader from '@/Components/AdminHeader.vue'
import { ref } from 'vue'
import { Head, Link, useForm, router } from '@inertiajs/vue3'

const props = defineProps({
    trips: Array,
    routesList: Array,
    driversList: Array,
    conductorsList: Array,
    busesList: Array,
    flash: Object
})

const showCreateTripModal = ref(false)
const showCounterModal = ref(false)
const showPriceModal = ref(false)
const selectedTripForCounter = ref(null)
const selectedTripForPrice = ref(null)

const tripForm = useForm({
    route_id: '',
    bus_id: '',
    driver_id: '',
    conductor_id: '',
    departure_time: '',
    arrival_time: '',
    base_price: 150000,
    floor1_price: 150000,
    floor2_price: 140000,
    floor1_seat_type: 'Giường Nằm VIP (Tầng 1)',
    floor2_seat_type: 'Giường Nằm Tiêu Chuẩn (Tầng 2)'
})

const priceForm = useForm({
    floor1_price: '',
    floor2_price: '',
    floor1_seat_type: '',
    floor2_seat_type: '',
    reason: 'Phụ thu dịp Lễ / Điều chỉnh giá ngày thường'
})

const counterForm = useForm({
    trip_id: '',
    customer_name: '',
    customer_phone: '',
    pickup_stop_id: '',
    dropoff_stop_id: '',
    seat_number: '',
    payment_method: 'CASH'
})

const submitTrip = () => {
    tripForm.post('/admin/trips', {
        onSuccess: () => {
            showCreateTripModal.value = false
            tripForm.reset()
        }
    })
}

const openPriceModal = (trip) => {
    selectedTripForPrice.value = trip
    priceForm.floor1_price = trip.floor1_price || trip.base_price
    priceForm.floor2_price = trip.floor2_price || trip.base_price
    priceForm.floor1_seat_type = trip.floor1_seat_type || 'Giường Nằm VIP (Tầng 1)'
    priceForm.floor2_seat_type = trip.floor2_seat_type || 'Giường Nằm Tiêu Chuẩn (Tầng 2)'
    showPriceModal.value = true
}

const submitPrice = () => {
    if (!selectedTripForPrice.value) return
    priceForm.post(`/admin/trips/${selectedTripForPrice.value.id}/price`, {
        onSuccess: () => {
            showPriceModal.value = false
        }
    })
}

const openCounterForTrip = (trip) => {
    selectedTripForCounter.value = trip
    counterForm.trip_id = trip.id
    const r = props.routesList?.find(x => x.id === trip.route_id)
    if (r && r.stops && r.stops.length >= 2) {
        counterForm.pickup_stop_id = r.stops[0].id
        counterForm.dropoff_stop_id = r.stops[r.stops.length - 1].id
    }
    showCounterModal.value = true
}

const submitCounter = () => {
    counterForm.post('/admin/counter-booking', {
        onSuccess: () => {
            showCounterModal.value = false
            counterForm.reset()
        }
    })
}

const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val || 0)
</script>

<template>
    <Head title="Quản Lý Chuyến Xe - FUTA Admin" />

    <div class="min-h-screen bg-slate-900 text-slate-100 pb-20">
        <!-- Top App Bar -->
        <AdminHeader title="QUẢN LÝ CHUYẾN XE" subtitle="Điều phối Chuyến xe, Định giá tầng & Sửa giá dịp Lễ Tết" />

        <div class="max-w-7xl mx-auto px-4 py-6 space-y-6">
            <!-- Flash Message -->
            <div v-if="$page.props.flash?.success" class="p-4 bg-emerald-500/20 border border-emerald-500/40 rounded-xl text-emerald-300 text-sm font-semibold flex items-center space-x-2">
                <span>✅</span>
                <span>{{ $page.props.flash.success }}</span>
            </div>
            <div v-if="$page.props.flash?.error" class="p-4 bg-rose-500/20 border border-rose-500/40 rounded-xl text-rose-300 text-sm font-semibold flex items-center space-x-2">
                <span>⚠️</span>
                <span>{{ $page.props.flash.error }}</span>
            </div>

            <!-- Control Bar -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-800 p-5 rounded-2xl border border-slate-700 shadow-xl">
                <div>
                    <h2 class="text-xl font-bold text-white">Danh Sách Chuyến Xe FUTA</h2>
                    <p class="text-xs text-slate-400">Mỗi chuyến xe có cấu hình giá riêng theo từng tầng (nằm/ngồi), dễ dàng tăng giá dịp lễ hoặc giảm giá ngày thường</p>
                </div>
                <div class="flex items-center space-x-3">
                    <button 
                        @click="showCreateTripModal = true"
                        class="px-5 py-2.5 bg-orange-600 hover:bg-orange-500 text-white font-bold rounded-xl shadow-lg shadow-orange-600/30 transition flex items-center space-x-2">
                        <span class="text-lg">➕</span>
                        <span>TẠO CHUYẾN XE MỚI</span>
                    </button>
                </div>
            </div>

            <!-- Trips Table -->
            <div class="bg-slate-800 rounded-2xl border border-slate-700 shadow-xl overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-300">
                        <thead class="bg-slate-900/80 text-slate-400 uppercase font-semibold border-b border-slate-700">
                            <tr>
                                <th class="p-4">Mã Chuyến</th>
                                <th class="p-4">Tuyến Đường</th>
                                <th class="p-4">Giá Từng Tầng (Nằm/Ngồi)</th>
                                <th class="p-4">Xe / Tài Xế / Lơ Xe</th>
                                <th class="p-4">Khởi Hành</th>
                                <th class="p-4">Trạng Thái</th>
                                <th class="p-4 text-right">Thao Tác</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-700/60">
                            <tr v-for="t in trips" :key="t.id" class="hover:bg-slate-700/30 transition">
                                <td class="p-4 font-mono font-bold text-orange-400">
                                    {{ t.trip_code }}
                                </td>
                                <td class="p-4">
                                    <div class="font-bold text-white text-sm">{{ t.route_name }}</div>
                                    <div class="text-slate-400">{{ t.total_passengers }} / {{ t.total_seats }} vé</div>
                                </td>
                                <td class="p-4">
                                    <div class="text-orange-400 font-bold">T1 ({{ t.floor1_seat_type }}): {{ formatCurrency(t.floor1_price) }}</div>
                                    <div class="text-indigo-400 font-bold mt-0.5">T2 ({{ t.floor2_seat_type }}): {{ formatCurrency(t.floor2_price) }}</div>
                                </td>
                                <td class="p-4">
                                    <div class="font-bold text-white">{{ t.bus_plate }}</div>
                                    <div class="text-slate-400">Tài xế: {{ t.driver_name }}</div>
                                    <div class="text-slate-400">Lơ xe: {{ t.conductor_name }}</div>
                                </td>
                                <td class="p-4 font-semibold text-slate-200">
                                    {{ t.departure_time }}
                                </td>
                                <td class="p-4">
                                    <span class="px-2 py-1 rounded text-xs font-bold uppercase"
                                        :class="{
                                            'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30': t.status === 'OPEN_FOR_SALE' || t.status === 'SCHEDULED',
                                            'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30': t.status === 'IN_TRANSIT',
                                            'bg-blue-500/20 text-blue-400 border border-blue-500/30': t.status === 'ARRIVED',
                                            'bg-purple-500/20 text-purple-400 border border-purple-500/30': t.status === 'CLOSED',
                                        }">
                                        {{ t.status }}
                                    </span>
                                </td>
                                <td class="p-4 text-right space-x-1.5">
                                    <button 
                                        @click="openPriceModal(t)"
                                        class="px-2.5 py-1.5 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg font-bold text-xs shadow transition">
                                        ✏️ Sửa Giá Dịp Lễ
                                    </button>
                                    <button 
                                        @click="openCounterForTrip(t)"
                                        class="px-2.5 py-1.5 bg-orange-600 hover:bg-orange-500 text-white rounded-lg font-bold text-xs shadow transition">
                                        + Bán Quầy
                                    </button>
                                    <Link 
                                        :href="`/trips/${t.id}`"
                                        class="px-2.5 py-1.5 bg-slate-700 hover:bg-slate-600 text-white rounded-lg font-bold text-xs transition">
                                        Sơ đồ ghế
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- MODAL 1: TẠO CHUYẾN XE MỚI VỚI GIÁ TỪNG TẦNG -->
        <div v-if="showCreateTripModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-slate-800 border border-slate-700 rounded-2xl max-w-xl w-full p-6 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-700">
                    <h3 class="text-lg font-bold text-white">🚍 Tạo Chuyến Xe Mới & Định Giá Từng Tầng</h3>
                    <button @click="showCreateTripModal = false" class="text-slate-400 hover:text-white text-xl">&times;</button>
                </div>

                <form @submit.prevent="submitTrip" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Chọn Tuyến Đường</label>
                        <select v-model="tripForm.route_id" required class="w-full bg-slate-900 border border-slate-600 rounded-xl px-3 py-2 text-white">
                            <option value="">-- Chọn tuyến chạy --</option>
                            <option v-for="r in routesList" :key="r.id" :value="r.id">
                                {{ r.name }} ({{ r.distance_km }} km)
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Gán Xe Chạy</label>
                            <select v-model="tripForm.bus_id" required class="w-full bg-slate-900 border border-slate-600 rounded-xl px-3 py-2 text-white">
                                <option value="">-- Chọn xe --</option>
                                <option v-for="b in busesList" :key="b.id" :value="b.id">
                                    {{ b.license_plate }} - {{ b.bus_type }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Gán Tài Xế Lái (8 Tài xế có sẵn)</label>
                            <select v-model="tripForm.driver_id" required class="w-full bg-slate-900 border border-slate-600 rounded-xl px-3 py-2 text-white">
                                <option value="">-- Chọn tài xế --</option>
                                <option v-for="d in driversList" :key="d.id" :value="d.id">
                                    {{ d.name }} ({{ d.phone }} - {{ d.license_class }})
                                </option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Gán Lơ Xe Phụ Trách (10 Lơ xe có sẵn)</label>
                            <select v-model="tripForm.conductor_id" class="w-full bg-slate-900 border border-slate-600 rounded-xl px-3 py-2 text-white">
                                <option value="">-- Chọn lơ xe --</option>
                                <option v-for="c in conductorsList" :key="c.id" :value="c.id">
                                    {{ c.name }} ({{ c.phone }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Giá Vé Cơ Sở Chung (VNĐ)</label>
                            <input v-model="tripForm.base_price" required type="number" min="10000" class="w-full bg-slate-900 border border-slate-600 rounded-xl px-3 py-2 text-white" />
                        </div>
                    </div>

                    <!-- Config Floor Prices & Types -->
                    <div class="p-3 bg-slate-900/80 rounded-xl border border-slate-700 space-y-3">
                        <div class="font-bold text-orange-400">Thiết Lập Giá & Loại Ghế Cho Từng Tầng:</div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-300 mb-1">Tên loại ghế Tầng 1</label>
                                <input v-model="tripForm.floor1_seat_type" placeholder="Giường Nằm VIP (Tầng 1)" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-3 py-2 text-white" />
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-300 mb-1">Giá vé Tầng 1 (VNĐ)</label>
                                <input v-model="tripForm.floor1_price" required type="number" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-3 py-2 text-white font-bold text-orange-400" />
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-semibold text-slate-300 mb-1">Tên loại ghế Tầng 2</label>
                                <input v-model="tripForm.floor2_seat_type" placeholder="Giường Nằm Tiêu Chuẩn (Tầng 2)" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-3 py-2 text-white" />
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-300 mb-1">Giá vé Tầng 2 (VNĐ)</label>
                                <input v-model="tripForm.floor2_price" required type="number" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-3 py-2 text-white font-bold text-indigo-400" />
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Thời Gian Khởi Hành</label>
                            <input v-model="tripForm.departure_time" required type="datetime-local" class="w-full bg-slate-900 border border-slate-600 rounded-xl px-3 py-2 text-white" />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Thời Gian Dự Kiến Đến</label>
                            <input v-model="tripForm.arrival_time" required type="datetime-local" class="w-full bg-slate-900 border border-slate-600 rounded-xl px-3 py-2 text-white" />
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-700 flex justify-end space-x-3">
                        <button type="button" @click="showCreateTripModal = false" class="px-4 py-2 bg-slate-700 text-slate-300 rounded-xl font-bold">Hủy</button>
                        <button type="submit" :disabled="tripForm.processing" class="px-5 py-2 bg-orange-600 hover:bg-orange-500 text-white font-bold rounded-xl shadow-lg transition">
                            {{ tripForm.processing ? 'Đang tạo...' : 'Xác Nhận Tạo Chuyến' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 2: ĐIỀU CHỈNH GIÁ VÉ DỊP LỄ / ĐỔI GIÁ -->
        <div v-if="showPriceModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-slate-800 border border-slate-700 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-700">
                    <h3 class="text-lg font-bold text-white">✏️ Sửa Giá Vé Chuyến {{ selectedTripForPrice?.trip_code }}</h3>
                    <button @click="showPriceModal = false" class="text-slate-400 hover:text-white text-xl">&times;</button>
                </div>

                <div class="text-xs text-slate-300">
                    <div>Tuyến: <strong class="text-white">{{ selectedTripForPrice?.route_name }}</strong></div>
                    <div>Xe: <strong class="text-white">{{ selectedTripForPrice?.bus_plate }}</strong></div>
                </div>

                <form @submit.prevent="submitPrice" class="space-y-4 text-xs">
                    <div class="p-3 bg-slate-900/80 rounded-xl border border-slate-700 space-y-3">
                        <div>
                            <label class="block font-semibold text-orange-400 mb-1">Giá Vé Tầng 1 (Dưới / VIP) - VNĐ</label>
                            <input v-model="priceForm.floor1_price" required type="number" min="10000" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-3 py-2 text-white font-bold text-sm" />
                        </div>
                        <div>
                            <label class="block font-semibold text-indigo-400 mb-1">Giá Vé Tầng 2 (Trên) - VNĐ</label>
                            <input v-model="priceForm.floor2_price" required type="number" min="10000" class="w-full bg-slate-800 border border-slate-600 rounded-xl px-3 py-2 text-white font-bold text-sm" />
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-slate-300 mb-1">Lý do điều chỉnh giá</label>
                        <input v-model="priceForm.reason" type="text" placeholder="Phụ thu Lễ 30/4, Tết Dương Lịch hoặc hết lễ giảm về giá chuẩn..." class="w-full bg-slate-900 border border-slate-600 rounded-xl px-3 py-2 text-white" />
                    </div>

                    <div class="pt-3 border-t border-slate-700 flex justify-end space-x-3">
                        <button type="button" @click="showPriceModal = false" class="px-4 py-2 bg-slate-700 text-slate-300 rounded-xl font-bold">Hủy</button>
                        <button type="submit" :disabled="priceForm.processing" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 text-white font-bold rounded-xl shadow-lg transition">
                            {{ priceForm.processing ? 'Đang lưu...' : 'Lưu Thay Đổi Giá' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 3: BÁN VÉ TẠI QUẦY (THU 100%) -->
        <div v-if="showCounterModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-slate-800 border border-slate-700 rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-700">
                    <h3 class="text-lg font-bold text-white">🎟️ Bán Vé Tại Quầy (Chuyến {{ selectedTripForCounter?.trip_code }})</h3>
                    <button @click="showCounterModal = false" class="text-slate-400 hover:text-white text-xl">&times;</button>
                </div>

                <form @submit.prevent="submitCounter" class="space-y-4 text-xs">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Họ Tên Khách Hàng</label>
                            <input v-model="counterForm.customer_name" required type="text" placeholder="Nguyễn Văn A" class="w-full bg-slate-900 border border-slate-600 rounded-xl px-3 py-2 text-white" />
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Số Điện Thoại</label>
                            <input v-model="counterForm.customer_phone" required type="text" placeholder="090xxxxxxx" class="w-full bg-slate-900 border border-slate-600 rounded-xl px-3 py-2 text-white" />
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Chọn Ghế</label>
                            <select v-model="counterForm.seat_number" required class="w-full bg-slate-900 border border-slate-600 rounded-xl px-3 py-2 text-white">
                                <option value="">-- Chọn ghế --</option>
                                <option v-for="i in 18" :key="'A' + i" :value="'A' + (i < 10 ? '0' + i : i)">
                                    Ghế A{{ i < 10 ? '0' + i : i }} (Tầng 1 - {{ formatCurrency(selectedTripForCounter?.floor1_price) }})
                                </option>
                                <option v-for="i in 18" :key="'B' + i" :value="'B' + (i < 10 ? '0' + i : i)">
                                    Ghế B{{ i < 10 ? '0' + i : i }} (Tầng 2 - {{ formatCurrency(selectedTripForCounter?.floor2_price) }})
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-300 mb-1">Hình Thức Thu Tiền (Thu đủ 100%)</label>
                            <select v-model="counterForm.payment_method" class="w-full bg-slate-900 border border-slate-600 rounded-xl px-3 py-2 text-white">
                                <option value="CASH">Tiền mặt tại quầy (Đã thu)</option>
                                <option value="TRANSFER">Chuyển khoản QR ngân hàng (Đã thu)</option>
                            </select>
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-700 flex justify-end space-x-3">
                        <button type="button" @click="showCounterModal = false" class="px-4 py-2 bg-slate-700 text-slate-300 rounded-xl font-bold">Hủy</button>
                        <button type="submit" :disabled="counterForm.processing" class="px-5 py-2 bg-orange-600 hover:bg-orange-500 text-white font-bold rounded-xl shadow-lg transition">
                            {{ counterForm.processing ? 'Đang xuất vé...' : 'Xuất Vé & Thu Đủ Tiền' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
