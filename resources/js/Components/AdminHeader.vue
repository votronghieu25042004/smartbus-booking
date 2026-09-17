<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    title: {
        type: String,
        default: 'FUTA QUẢN TRỊ TỔNG'
    },
    subtitle: {
        type: String,
        default: 'Điều phối toàn bộ nhà xe: Tuyến, Xe, Tài xế, Lơ xe, Doanh thu'
    }
})

const page = usePage()
const currentPath = computed(() => (page.url || '').split('?')[0])

const navItems = [
    { name: 'Dashboard', path: '/admin', icon: '📊', exact: true },
    { name: 'Chuyến xe', path: '/admin/trips', icon: '🚌' },
    { name: 'Tuyến đường', path: '/admin/routes', icon: '🛣️' },
    { name: 'Đội xe', path: '/admin/buses', icon: '🚍' },
    { name: 'Tài xế (8)', path: '/admin/drivers', icon: '👨‍✈️' },
    { name: 'Lơ xe (10)', path: '/admin/users', icon: '👥' },
    { name: 'Tài chính', path: '/admin/finance', icon: '💰' },
    { name: 'App Tài xế', path: '/driver', icon: '👨‍✈️', highlight: true },
    { name: 'App Lơ xe', path: '/conductor', icon: '📱', highlight: true },
    { name: 'Trang chủ', path: '/', icon: '🏠' },
]

const isActive = (item) => {
    if (item.exact || item.path === '/admin') {
        return currentPath.value === '/admin'
    }
    return currentPath.value.startsWith(item.path)
}
</script>

<template>
    <header class="bg-slate-900/95 backdrop-blur-md border-b border-slate-700/80 sticky top-0 z-50 px-4 py-3 shadow-2xl">
        <div class="max-w-7xl mx-auto flex flex-col lg:flex-row lg:items-center justify-between gap-3">
            <!-- Brand & Info -->
            <div class="flex items-center space-x-3">
                <Link href="/admin" class="w-10 h-10 rounded-xl bg-gradient-to-tr from-orange-600 to-amber-500 flex items-center justify-center font-black text-white text-xl shadow-lg shadow-orange-600/40 shrink-0 hover:scale-105 transition transform">
                    F
                </Link>
                <div>
                    <div class="flex items-center space-x-2">
                        <h1 class="text-base font-black text-white leading-tight tracking-wide">{{ title }}</h1>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-rose-500/20 text-rose-400 border border-rose-500/40 uppercase tracking-wider">
                            SUPER ADMIN
                        </span>
                    </div>
                    <p class="text-[11px] text-slate-400 hidden sm:block">{{ subtitle }}</p>
                </div>
            </div>

            <!-- Unified Persistent Navigation Tabs -->
            <nav class="flex items-center flex-wrap gap-1.5 overflow-x-auto pb-1 lg:pb-0">
                <Link 
                    v-for="item in navItems" 
                    :key="item.path"
                    :href="item.path"
                    class="px-3 py-1.5 rounded-xl text-xs font-bold transition-all duration-200 flex items-center space-x-1.5 shrink-0 select-none"
                    :class="{
                        'bg-orange-600 text-white shadow-lg shadow-orange-600/50 ring-2 ring-orange-400 font-extrabold scale-105': isActive(item),
                        'bg-emerald-600 hover:bg-emerald-500 text-white shadow-md shadow-emerald-900/30': !isActive(item) && item.highlight,
                        'bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 hover:border-slate-500': !isActive(item) && !item.highlight
                    }">
                    <span>{{ item.icon }}</span>
                    <span>{{ item.name }}</span>
                </Link>
            </nav>
        </div>
    </header>
</template>
