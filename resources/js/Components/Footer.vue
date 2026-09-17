<template>
  <footer class="bg-slate-950 border-t border-slate-800 text-slate-400 pt-16 pb-12 mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-10 mb-12">
        <div class="space-y-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-amber-500 to-orange-500 flex items-center justify-center text-white font-black text-xl">
              SB
            </div>
            <span class="text-2xl font-black text-white">SmartBus</span>
          </div>
          <p class="text-sm text-slate-400 leading-relaxed">
            Hệ thống đặt vé xe khách liên tỉnh toàn quốc tích hợp Trí tuệ nhân tạo (AI) và Soát vé mã QR Code thông minh.
          </p>
          <div class="text-xs text-slate-500">
            © 2026 SmartBus Platform. All rights reserved.
          </div>
        </div>

        <div>
          <h4 class="text-white font-bold mb-4 text-base">Tuyến đường nổi bật</h4>
          <ul class="space-y-2.5 text-sm">
            <li><a href="/trips?from_province_id=1&to_province_id=2" class="hover:text-orange-400 transition-colors">Xe Đà Nẵng đi Quảng Trị (Đông Hà)</a></li>
            <li><a href="/trips?from_province_id=3&to_province_id=2" class="hover:text-orange-400 transition-colors">Xe Huế đi Quảng Trị (Đông Hà)</a></li>
            <li><a href="/trips?from_province_id=1&to_province_id=4" class="hover:text-orange-400 transition-colors">Xe Đà Nẵng đi Hà Nội</a></li>
            <li><a href="/trips?from_province_id=1&to_province_id=5" class="hover:text-orange-400 transition-colors">Xe Đà Nẵng đi TP. Hồ Chí Minh</a></li>
          </ul>
        </div>

        <div>
          <h4 class="text-white font-bold mb-4 text-base">Chính sách & Hỗ trợ</h4>
          <ul class="space-y-2.5 text-sm">
            <li><a href="#cancellation-policy" class="hover:text-orange-400 transition-colors">Quy định đặt cọc 30%</a></li>
            <li><a href="#cancellation-policy" class="hover:text-orange-400 transition-colors">Chính sách hoàn tiền & hủy vé</a></li>
            <li><a href="/scanner" class="hover:text-orange-400 transition-colors">Cổng soát vé cho nhân viên</a></li>
            <li><span class="hover:text-orange-400 transition-colors">Hướng dẫn quét QR lên xe</span></li>
          </ul>
        </div>

        <div>
          <h4 class="text-white font-bold mb-4 text-base">Liên hệ & Hỗ trợ</h4>
          <p class="text-sm mb-2 text-slate-300">Hotline đặt vé & phản ánh:</p>
          <p class="text-2xl font-black text-orange-400 mb-4">1900 8888</p>
          <p class="text-xs text-slate-400">Email: hotro@smartbus.vn</p>
          <p class="text-xs text-slate-400 mt-1">Trụ sở: 185 Tôn Đức Thắng, Liên Chiểu, Đà Nẵng</p>
        </div>
      </div>
    </div>
  </footer>
</template>
