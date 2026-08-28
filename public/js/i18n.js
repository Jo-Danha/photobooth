/* Sistem multi-bahasa sederhana (ID / EN) untuk Photobooth.
   Elemen dengan atribut data-i18n="key" akan diterjemahkan.
   Pilihan disimpan di localStorage dan diterapkan saat load. */
window.I18N_DICT = {
    id: {
        'app.tagline': 'Self-Photo Studio',
        'booth.step': 'Langkah 1 dari 3: Pilih Template',
        'booth.hero_title': 'Pilih Format & Layout Foto Favoritmu',
        'booth.hero_sub': 'Geser ke kiri/kanan untuk melihat semua template. Bayar instan dengan QRIS, nikmati sesi foto bebas bergaya!',
        'booth.cta': 'Lanjut ke Pembayaran QRIS',
        'booth.gallery': 'Gallery Hasil',
        'studio.viewfinder': 'Live Viewfinder',
        'studio.shots': 'Foto',
        'studio.autoshoot': 'Jepret Otomatis (3s)',
        'studio.manual': '1x Jepret',
        'studio.results': 'Hasil Foto',
        'studio.reset': 'Ulang',
        'studio.proceed': 'Lanjut ke Kustomisasi Frame',
        'studio.preview': 'Preview Strip',
        'studio.retake': 'Foto Ulang',
        'studio.frame_color': '1. Warna Bingkai',
        'studio.filter': '2. Filter Visual',
        'studio.stickers': '3. Stiker Digital',
        'studio.clear_stickers': 'Hapus Semua',
        'studio.custom_text': 'Teks Kustom',
        'studio.date_stamp': 'Stempel Tanggal',
        'studio.time_stamp': 'Stempel Waktu',
        'studio.shape': 'Bentuk Foto',
        'studio.shape_none': 'Kotak',
        'studio.shape_soft': 'Soft',
        'studio.shape_circle': 'Lingkaran',
        'studio.shape_heart': 'Hati',
        'studio.logo': 'Logo Watermark',
        'studio.save': 'Selesai & Simpan Foto',
        'studio.timer': 'Sisa Waktu',
        'studio.event_mode': 'Mode Event - Tanpa Batas Waktu',
        'gallery.title': 'Gallery Hasil Photobooth',
        'gallery.empty': 'Belum ada foto yang tersimpan.',
        'common.back': 'Kembali ke Menu Utama',
        'common.retake': 'Foto Ulang',
        'common.done': 'Selesai'
    },
    en: {
        'app.tagline': 'Self-Photo Studio',
        'booth.step': 'Step 1 of 3: Choose Template',
        'booth.hero_title': 'Pick Your Favorite Photo Format & Layout',
        'booth.hero_sub': 'Swipe left/right to see all templates. Pay instantly with QRIS, enjoy a free-style photo session!',
        'booth.cta': 'Continue to QRIS Payment',
        'booth.gallery': 'Results Gallery',
        'studio.viewfinder': 'Live Viewfinder',
        'studio.shots': 'Photo',
        'studio.autoshoot': 'Auto Snap (3s)',
        'studio.manual': '1x Snap',
        'studio.results': 'Photo Results',
        'studio.reset': 'Reset',
        'studio.proceed': 'Continue to Frame Customization',
        'studio.preview': 'Strip Preview',
        'studio.retake': 'Retake Photos',
        'studio.frame_color': '1. Frame Color',
        'studio.filter': '2. Visual Filter',
        'studio.stickers': '3. Digital Stickers',
        'studio.clear_stickers': 'Clear All',
        'studio.custom_text': 'Custom Text',
        'studio.date_stamp': 'Date Stamp',
        'studio.time_stamp': 'Time Stamp',
        'studio.shape': 'Photo Shape',
        'studio.shape_none': 'Square',
        'studio.shape_soft': 'Soft',
        'studio.shape_circle': 'Circle',
        'studio.shape_heart': 'Heart',
        'studio.logo': 'Logo Watermark',
        'studio.save': 'Finish & Save Photo',
        'studio.timer': 'Time Left',
        'studio.event_mode': 'Event Mode - Unlimited Time',
        'gallery.title': 'Photobooth Results Gallery',
        'gallery.empty': 'No photos saved yet.',
        'common.back': 'Back to Main Menu',
        'common.retake': 'Retake',
        'common.done': 'Done'
    }
};

window.getBoothLang = function () {
    const saved = localStorage.getItem('booth_lang');
    if (saved) return saved;
    return (document.documentElement.getAttribute('data-default-lang') || 'id');
};

window.applyI18n = function () {
    const lang = window.getBoothLang();
    const dict = window.I18N_DICT[lang] || window.I18N_DICT.id;
    document.querySelectorAll('[data-i18n]').forEach(function (el) {
        const key = el.getAttribute('data-i18n');
        if (dict[key] !== undefined) {
            el.innerHTML = dict[key];
        }
    });
    document.querySelectorAll('[data-lang-label]').forEach(function (el) {
        el.innerText = (lang === 'id') ? 'EN' : 'ID';
    });
    document.documentElement.lang = lang;
};

window.toggleBoothLang = function () {
    const cur = window.getBoothLang();
    localStorage.setItem('booth_lang', cur === 'id' ? 'en' : 'id');
    window.applyI18n();
};

document.addEventListener('DOMContentLoaded', window.applyI18n);
