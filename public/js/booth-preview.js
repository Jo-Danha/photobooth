/* Render contoh hasil (preview) tiap layout di halaman booth.
   Menampilkan foto sample + dekorasi tema + teks brand sehingga
   pengunjung langsung tahu "hasilnya nanti seperti ini".
   Bila gambar sample gagal dimuat, tetap digambar placeholder agar
   frame layout tidak pernah kosong. */
(function () {
    const FRAME_SIZES = {
        strip_4: { w: 400, h: 1164 }, strip_3: { w: 400, h: 906 }, strip_2: { w: 400, h: 648 },
        hearts: { w: 400, h: 1164 }, dog: { w: 400, h: 1164 }, vintage: { w: 400, h: 1164 },
        solace: { w: 400, h: 1164 }, classic: { w: 400, h: 1164 }, with_love: { w: 400, h: 1164 },
        holidays: { w: 400, h: 1164 },
        grid_4: { w: 678, h: 618 }, strip_e: { w: 678, h: 618 }, strip_6: { w: 678, h: 861 },
        polaroid: { w: 420, h: 500 }
    };

    const LAYOUT_CONFIG = {
        strip_4: { cols: 1, rows: 4 }, strip_3: { cols: 1, rows: 3 }, strip_2: { cols: 1, rows: 2 },
        strip_6: { cols: 2, rows: 3 }, strip_e: { cols: 2, rows: 2 }, grid_4: { cols: 2, rows: 2 },
        polaroid: { cols: 1, rows: 1, polaroid: true },
        hearts: { cols: 1, rows: 4, theme: 'hearts' }, dog: { cols: 1, rows: 4, theme: 'dog' },
        vintage: { cols: 1, rows: 4, theme: 'vintage' }, solace: { cols: 1, rows: 4, theme: 'solace' },
        classic: { cols: 1, rows: 4, theme: 'classic' }, with_love: { cols: 1, rows: 4, theme: 'with_love' },
        holidays: { cols: 1, rows: 4, theme: 'holidays' }
    };

    const PLACEHOLDER_COLORS = [
        ['#FBCFE8', '#DB2777'], ['#BFDBFE', '#2563EB'], ['#BBF7D0', '#16A34A'],
        ['#FECACA', '#DC2626'], ['#DDD6FE', '#7C3AED'], ['#FDE68A', '#D97706']
    ];

    function drawPhotoCover(ctx, img, x, y, w, h) {
        const ir = img.width / img.height;
        const r = w / h;
        let sx, sy, sw, sh;
        if (ir > r) { sh = img.height; sw = sh * r; sx = (img.width - sw) / 2; sy = 0; }
        else { sw = img.width; sh = sw / r; sx = 0; sy = (img.height - sh) / 2; }
        ctx.drawImage(img, sx, sy, sw, sh, x, y, w, h);
    }

    function drawPlaceholder(ctx, x, y, w, h, i) {
        const c = PLACEHOLDER_COLORS[i % PLACEHOLDER_COLORS.length];
        const grad = ctx.createLinearGradient(x, y, x, y + h);
        grad.addColorStop(0, c[0]); grad.addColorStop(1, c[1]);
        ctx.fillStyle = grad;
        const r = 8;
        ctx.beginPath();
        ctx.moveTo(x + r, y);
        ctx.arcTo(x + w, y, x + w, y + h, r);
        ctx.arcTo(x + w, y + h, x, y + h, r);
        ctx.arcTo(x, y + h, x, y, r);
        ctx.arcTo(x, y, x + w, y, r);
        ctx.closePath();
        ctx.fill();
        // silhouette kepala + bahu biar terlihat seperti foto
        ctx.fillStyle = 'rgba(255,255,255,0.55)';
        const cx = x + w / 2, headR = h * 0.14;
        ctx.beginPath(); ctx.arc(cx, y + h * 0.36, headR, 0, Math.PI * 2); ctx.fill();
        ctx.beginPath();
        ctx.ellipse(cx, y + h * 0.78, w * 0.30, h * 0.26, 0, 0, Math.PI * 2);
        ctx.fill();
    }

    function drawThemeDecoration(ctx, theme, darkFrame, W, H) {
        const accent = darkFrame ? '#F9A8D4' : '#DB2777';
        if (theme === 'hearts') {
            ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
            const hearts = ['❤', '♡', '💗'];
            ctx.font = '26px Arial';
            for (let i = 0; i < 6; i++) {
                ctx.fillText(hearts[i % hearts.length], 18, 70 + i * 55);
                ctx.fillText(hearts[(i + 1) % hearts.length], W - 18, 70 + i * 55);
            }
            ctx.font = '30px Arial'; ctx.fillText('💗', W / 2, H - 62);
            ctx.textBaseline = 'alphabetic';
        } else if (theme === 'dog') {
            ctx.textAlign = 'center'; ctx.textBaseline = 'middle'; ctx.font = '30px Arial';
            ctx.fillText('🐾', 22, 60); ctx.fillText('🐶', W - 22, 60);
            ctx.fillText('🦴', 22, H - 70); ctx.fillText('🐾', W - 22, H - 70);
            ctx.font = '24px Arial';
            for (let i = 0; i < 4; i++) ctx.fillText('🐾', W / 2 + (i % 2 ? 40 : -40), 90 + i * 50);
            ctx.textBaseline = 'alphabetic';
        } else if (theme === 'vintage') {
            ctx.strokeStyle = darkFrame ? '#D6C7A1' : '#8B6B3A';
            ctx.lineWidth = 3; ctx.strokeRect(14, 14, W - 28, H - 28);
            ctx.lineWidth = 1; ctx.strokeRect(20, 20, W - 40, H - 40);
            ctx.fillStyle = darkFrame ? '#D6C7A1' : '#8B6B3A';
            ctx.textAlign = 'left'; ctx.font = 'italic 12px serif';
            ctx.fillText('est. ' + new Date().getFullYear(), 26, H - 34);
        } else if (theme === 'solace') {
            ctx.strokeStyle = darkFrame ? '#A5B4FC' : '#C4B5FD';
            ctx.lineWidth = 6; ctx.strokeRect(16, 16, W - 32, H - 32);
            ctx.fillStyle = darkFrame ? '#E0E7FF' : '#7C3AED';
            ctx.textAlign = 'center'; ctx.font = '22px Arial'; ctx.fillText('✦', W / 2, H - 60);
        } else if (theme === 'classic') {
            ctx.strokeStyle = darkFrame ? '#F1F5F9' : '#334155';
            ctx.lineWidth = 2; ctx.strokeRect(16, 16, W - 32, H - 32);
            ctx.lineWidth = 1; ctx.strokeRect(22, 22, W - 44, H - 44);
            ctx.fillStyle = darkFrame ? '#F1F5F9' : '#334155';
            ctx.textAlign = 'center'; ctx.font = '16px Arial';
            ctx.fillText('◆', 26, 28); ctx.fillText('◆', W - 26, 28);
            ctx.fillText('◆', 26, H - 26); ctx.fillText('◆', W - 26, H - 26);
        } else if (theme === 'with_love') {
            ctx.strokeStyle = accent; ctx.lineWidth = 2;
            ctx.setLineDash([8, 6]); ctx.strokeRect(16, 16, W - 32, H - 32); ctx.setLineDash([]);
            ctx.textAlign = 'center'; ctx.textBaseline = 'middle'; ctx.font = '24px Arial';
            ctx.fillText('♡', W / 2, 60); ctx.textBaseline = 'alphabetic';
        } else if (theme === 'holidays') {
            ctx.textAlign = 'center'; ctx.textBaseline = 'middle';
            const deco = ['❄', '⭐', '🎄', '✨'];
            ctx.font = '24px Arial';
            const pts = [[20, 60], [W - 20, 60], [20, H - 70], [W - 20, H - 70], [W / 2, 50], [W / 2, H - 60]];
            pts.forEach((p, i) => ctx.fillText(deco[i % deco.length], p[0], p[1]));
            ctx.strokeStyle = darkFrame ? '#93C5FD' : '#2563EB';
            ctx.lineWidth = 2; ctx.setLineDash([6, 6]); ctx.strokeRect(14, 14, W - 28, H - 28); ctx.setLineDash([]);
            ctx.textBaseline = 'alphabetic';
        }
    }

    function renderPreview(canvas, layoutId, img, opts) {
        const sz = FRAME_SIZES[layoutId] || { w: 400, h: 1164 };
        const cfg = LAYOUT_CONFIG[layoutId] || { cols: 1, rows: 4 };
        const W = sz.w, H = sz.h;
        canvas.width = W; canvas.height = H;
        const ctx = canvas.getContext('2d');
        const frameColor = opts.frameColor || '#FFFFFF';
        const darkFrame = (frameColor === '#111111' || frameColor === '#312E81');
        const brand = (opts.brand || 'PHOTOBOOTH').toUpperCase();

        ctx.fillStyle = frameColor;
        ctx.fillRect(0, 0, W, H);

        const margin = 30, spacing = 18, footerHeight = 90;
        const cols = cfg.cols, rows = cfg.rows;
        let pw, ph;
        if (cfg.polaroid) { pw = 360; ph = 360; }
        else { pw = cols === 1 ? 340 : 300; ph = cols === 1 ? 240 : 225; }

        const total = cols * rows;
        for (let i = 0; i < total; i++) {
            let x, y;
            if (cfg.polaroid) { x = margin; y = margin; }
            else {
                const c = i % cols, r = Math.floor(i / cols);
                x = margin + c * (pw + spacing);
                y = margin + r * (ph + spacing);
            }
            ctx.save();
            ctx.filter = i % 2 ? 'brightness(1.05) saturate(1.1)' : 'brightness(0.97)';
            if (img) drawPhotoCover(ctx, img, x, y, pw, ph);
            else drawPlaceholder(ctx, x, y, pw, ph, i);
            ctx.restore();
        }

        if (cfg.theme) drawThemeDecoration(ctx, cfg.theme, darkFrame, W, H);

        ctx.textAlign = 'center';
        const fY = cfg.polaroid ? H - 24 : H - 48;
        if (cfg.theme === 'with_love') {
            ctx.fillStyle = darkFrame ? '#FBCFE8' : '#BE185D';
            ctx.font = 'italic bold 22px cursive';
            ctx.fillText('with love ♡', W / 2, fY);
        } else {
            ctx.fillStyle = darkFrame ? '#FFFFFF' : '#1E293B';
            ctx.font = 'bold 16px sans-serif';
            ctx.fillText(brand, W / 2, fY);
        }

        if (cfg.polaroid) {
            ctx.fillStyle = darkFrame ? '#E2E8F0' : '#475569';
            ctx.font = 'italic 13px cursive';
            ctx.fillText(new Date().toLocaleDateString('id-ID', { year: 'numeric', month: 'short', day: 'numeric' }), W / 2, H - 8);
        }
    }

    window.initBoothPreviews = function (sampleSrc, opts) {
        const o = opts || {};
        function drawAll(img) {
            document.querySelectorAll('canvas[data-layout]').forEach(c => {
                try { renderPreview(c, c.getAttribute('data-layout'), img, o); }
                catch (e) { console.error('preview error', e); }
            });
        }
        // Gambar placeholder segera agar frame tidak pernah kosong
        drawAll(null);
        const img = new Image();
        img.onload = function () { drawAll(img); };
        img.onerror = function () { console.warn('sample image gagal dimuat, tetap pakai placeholder'); };
        img.src = sampleSrc;
    };
})();
