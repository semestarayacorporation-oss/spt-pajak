<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Smart SPT Pajak - Sistem Laporan Keuangan Otomatis</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        .currency-input { font-family: 'Courier New', monospace; font-weight: bold; letter-spacing: 0.5px; }
        .transition-all { transition: all 0.3s ease-in-out; }
        .balance-ok { border-color: #22c55e; background-color: #f0fdf4; }
        .balance-err { border-color: #ef4444; background-color: #fef2f2; animation: shake 0.5s; }
        @keyframes shake { 0%, 100% { transform: translateX(0); } 25% { transform: translateX(-5px); } 75% { transform: translateX(5px); } }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        /* Paper Effect */
        .paper-shadow { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05), inset 0 0 20px rgba(0,0,0,0.02); }
        .dashed-line { border-bottom: 2px dashed #cbd5e1; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans h-screen flex overflow-hidden">

    <!-- SIDEBAR -->
    <aside class="w-80 bg-slate-900 text-white flex flex-col shadow-2xl z-20 flex-shrink-0">
        <div class="p-6 border-b border-slate-700 bg-slate-950">
            <h2 class="text-xl font-bold flex items-center gap-2">
                <i data-lucide="zap" class="text-yellow-400"></i> Smart SPT Pajak
            </h2>
            <p class="text-xs text-slate-400 mt-1">Sistem Laporan Keuangan Otomatis</p>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-6">
            
            <!-- NERACA MONITOR -->
            <div id="balance-monitor" class="bg-slate-800 p-4 rounded-xl border border-slate-700">
                <p class="text-[10px] uppercase font-bold text-slate-400 mb-2">Status Neraca (L1)</p>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs text-slate-300">Aset:</span>
                    <span id="mon-aset" class="font-mono text-xs">0</span>
                </div>
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs text-slate-300">Passiva:</span>
                    <span id="mon-passiva" class="font-mono text-xs">0</span>
                </div>
                <div id="balance-badge" class="mt-2 w-full py-1 text-center rounded text-xs font-bold bg-gray-600 text-gray-300">
                    Belum Diisi
                </div>
                <p class="text-[9px] text-slate-500 mt-2 italic text-center">
                    Rumus: Aset = Kewajiban + Modal + Laba
                </p>
            </div>

            <!-- TAX MONITOR -->
            <div class="bg-gradient-to-br from-blue-900 to-slate-900 p-4 rounded-xl border border-blue-800 shadow-lg">
                <p class="text-xs text-blue-200 mb-1">Estimasi Bayar Pajaknya</p>
                <div class="text-2xl font-mono font-bold text-white tracking-tighter" id="live-tax">Rp 0</div>
                <div class="mt-2 pt-2 border-t border-blue-800/50 flex justify-between text-[10px] text-blue-300">
                    <span>Skema:</span>
                    <span id="active-scheme-label" class="font-bold text-white">-</span>
                </div>
            </div>

            <!-- NAV -->
            <nav class="space-y-1">
                <a href="#section-1" class="nav-item flex items-center gap-3 p-2 rounded hover:bg-slate-800 text-sm text-slate-300">
                    <span class="w-2 h-2 rounded-full bg-blue-500"></span> 1. Profil & Tarif Berlaku
                </a>
                <a href="#section-2" class="nav-item flex items-center gap-3 p-2 rounded hover:bg-slate-800 text-sm text-slate-300">
                    <span class="w-2 h-2 rounded-full bg-slate-600"></span> 2. Laba Rugi & Fiskal setahun ini
                </a>
                <a href="#section-neraca" class="nav-item flex items-center gap-3 p-2 rounded hover:bg-slate-800 text-sm text-slate-300">
                    <span class="w-2 h-2 rounded-full bg-slate-600"></span> 3. NERACA (Kesehatan keuangan)
                </a>
                <a href="#section-4" class="nav-item flex items-center gap-3 p-2 rounded hover:bg-slate-800 text-xs text-slate-300">
                    <span class="w-2 h-2 rounded-full bg-slate-600 flex-shrink-0"></span> 4. Kredit Yg pernah bayar/diPotong jd pengurang pjk
                </a>
            </nav>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 flex flex-col h-full relative bg-gray-100">
        <header class="bg-white p-4 border-b flex justify-between items-center px-8 shadow-sm z-10 sticky top-0">
            <h1 class="font-bold text-xl text-gray-800">Input Data SPT Tahunan</h1>
            <button onclick="downloadCSV()" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-bold flex items-center gap-2 shadow transition transform hover:scale-105">
                <i data-lucide="save"></i> Simpan & Download
            </button>
        </header>

        <div class="flex-1 overflow-y-auto p-8 pb-40 space-y-8 scroll-smooth" id="main-scroll">
            
            <!-- 1. PROFIL & TARIF -->
            <section id="section-1" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                <h3 class="font-bold text-gray-700 mb-4 border-b pb-2">1. Profil & Tarif Berlaku</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Bentuk Usaha</label>
                        <select id="inp-entity" class="w-full p-3 border rounded bg-white focus:ring-2 focus:ring-blue-500 outline-none" onchange="runEngine()">
                            <option value="PT">PT (Perseroan Terbatas)</option>
                            <option value="CV">CV (Persekutuan Komanditer)</option>
                            <option value="CV">Firma</option>
                            <option value="CV">Koperasi</option>
                            <option value="CV">Yayasan / Badan Nirlaba</option>
                            <option value="CV">BUMDes / BUMDesma</option>
                            <option value="CV">Persekutuan Perdata</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Tahun Terdaftar</label>
                        <input type="number" id="inp-reg-year" class="w-full p-3 border rounded focus:ring-2 focus:ring-blue-500 outline-none" value="2023" onkeyup="runEngine()">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1 text-blue-600">Pendapatan Setahun (OMZET Bruto)</label>
                        <input type="text" id="inp-omzet" class="currency-input w-full p-3 border-2 border-yellow-400 bg-yellow-50 rounded text-right focus:border-yellow-600 focus:bg-yellow-100 outline-none transition-colors" placeholder="0" onkeyup="formatAndReact(this)">
                    </div>
                </div>
                
                <!-- Tarif Selector -->
                <div class="flex gap-4">
                    <label id="card-umkm" class="flex-1 border-2 border-gray-200 p-4 rounded-xl cursor-pointer bg-gray-50 relative overflow-hidden transition-all hover:shadow-md">
                        <div class="flex items-center gap-3">
                            <input type="radio" name="scheme" value="UMKM" class="w-5 h-5 text-green-600" onchange="runEngine()">
                            <div>
                                <div class="font-bold text-gray-800">Final UMKM 0,5%</div>
                                <div class="text-xs text-gray-500">Dari Omzet Bruto</div>
                            </div>
                        </div>
                        <div id="blocker-umkm" class="hidden absolute inset-0 bg-gray-200/90 flex items-center justify-center text-xs font-bold text-red-600">
                            <i data-lucide="lock" class="w-3 h-3 mr-1"></i> Syarat Tidak Terpenuhi
                        </div>
                    </label>
                    <label id="card-umum" class="flex-1 border-2 border-blue-200 bg-blue-50 p-4 rounded-xl cursor-pointer transition-all hover:shadow-md">
                        <div class="flex items-center gap-3">
                            <input type="radio" name="scheme" value="UMUM" class="w-5 h-5 text-blue-600" checked onchange="runEngine()">
                            <div>
                                <div class="font-bold text-blue-900">Tarif Umum 11 & 22%</div>
                                <div class="text-xs text-blue-600">Laba Rugi (Pasal 17/31E)</div>
                            </div>
                        </div>
                    </label>
                </div>
            </section>

            <!-- 2. LABA RUGI -->
            <section id="section-2" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 transition-all">
                <h3 class="font-bold text-gray-700 mb-4 border-b pb-2 flex justify-between">
                    <span>2. Laba Rugi & Fiskal setahun ini</span>
                    <span id="badge-laba" class="text-xs bg-gray-100 px-2 py-1 rounded">Netto: Rp 0</span>
                </h3>
                
                <!-- INPUT FORM -->
                <div id="form-laba" class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">HP.Pokok BYR Keluar</label>
                        <input type="text" id="inp-hpp" class="currency-input w-full p-3 border rounded text-right focus:ring-2 focus:ring-blue-500 outline-none" placeholder="0" onkeyup="formatAndReact(this)">
                        <p class="text-[10px] text-blue-500 mt-1 italic"><i data-lucide="info" class="w-3 h-3 inline"></i> Estimasi wajar: 79% dari Omzet</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">PengGAJIan/upah</label>
                        <input type="text" id="inp-gaji" class="currency-input w-full p-3 border rounded text-right focus:ring-2 focus:ring-blue-500 outline-none" placeholder="0" onkeyup="formatAndReact(this)">
                        <p class="text-[10px] text-blue-500 mt-1 italic"><i data-lucide="info" class="w-3 h-3 inline"></i> Estimasi wajar: 10% dari Omzet</p>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Biaya Lainnya</label>
                        <input type="text" id="inp-biaya" class="currency-input w-full p-3 border rounded text-right focus:ring-2 focus:ring-blue-500 outline-none" placeholder="0" onkeyup="formatAndReact(this)">
                        <p class="text-[10px] text-blue-500 mt-1 italic"><i data-lucide="info" class="w-3 h-3 inline"></i> Estimasi wajar: 5% dari Omzet</p>
                    </div>
                    <div></div> <!-- Spacer -->
                    <div>
                        <label class="block text-xs font-bold text-red-500 uppercase mb-1">Evaluasi Positif (+)</label>
                        <input type="text" id="inp-kor-pos" class="currency-input w-full p-3 border border-red-100 rounded text-right focus:ring-2 focus:ring-red-500 outline-none" placeholder="0" onkeyup="formatAndReact(this)">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-green-500 uppercase mb-1">Evaluasi Negatif (-)</label>
                        <input type="text" id="inp-kor-neg" class="currency-input w-full p-3 border border-green-100 rounded text-right focus:ring-2 focus:ring-green-500 outline-none" placeholder="0" onkeyup="formatAndReact(this)">
                    </div>
                </div>

                <!-- PREVIEW LAPORAN LABA RUGI (PAPER EFFECT) -->
                <div class="relative bg-white paper-shadow border border-slate-100 p-8 rounded-sm max-w-2xl mx-auto">
                    <!-- Paper Header -->
                    <div class="text-center mb-6 border-b-2 border-double border-slate-800 pb-2">
                        <h4 class="font-serif font-bold text-lg text-slate-800">LAPORAN LABA RUGI</h4>
                        <p class="text-xs text-slate-500">Periode Berakhir 31 Desember</p>
                    </div>

                    <!-- Content -->
                    <div class="space-y-2 font-mono text-sm text-slate-700">
                        <div class="flex justify-between">
                            <span>Pendapatan Usaha</span>
                            <span class="font-bold" id="prev-omzet">0</span>
                        </div>
                        <div class="flex justify-between text-slate-500">
                            <span class="pl-4">Harga Pokok Penjualan (HPP)</span>
                            <span id="prev-hpp">(0)</span>
                        </div>
                        <div class="flex justify-between font-bold border-t border-slate-300 pt-1 pb-2">
                            <span>LABA KOTOR</span>
                            <span id="prev-laba-kotor">0</span>
                        </div>

                        <div class="text-xs text-slate-500 uppercase font-bold mt-2">Beban Operasional:</div>
                        <div class="flex justify-between pl-4">
                            <span>Beban Gaji & Upah</span>
                            <span id="prev-gaji">(0)</span>
                        </div>
                        <div class="flex justify-between pl-4">
                            <span>Beban Usaha Lainnya</span>
                            <span id="prev-biaya">(0)</span>
                        </div>
                        <div class="flex justify-between font-bold border-t border-slate-800 pt-2 mt-2 text-base">
                            <span>LABA BERSIH (KOMERSIAL)</span>
                            <span id="prev-laba-bersih">0</span>
                        </div>
                        
                        <div class="dashed-line my-4"></div>
                        
                        <!-- TAX CALCULATION (ESTIMATE) -->
                        <div class="text-xs text-slate-500 uppercase font-bold mt-2">Ketentuan Pajak (Estimasi):</div>
                        <div class="bg-yellow-50 p-3 rounded border border-yellow-100 space-y-2 mt-1">
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-600">Dasar Pengenaan Pajak (DPP)</span>
                                <span id="prev-dpp" class="font-mono font-bold">0</span>
                            </div>
                            <div class="flex justify-between text-xs">
                                <span class="text-slate-600">Tarif yang Berlaku</span>
                                <span id="prev-tarif" class="font-mono text-blue-600 font-bold">-</span>
                            </div>
                            <div class="flex justify-between text-sm font-bold border-t border-yellow-200 pt-2 text-slate-800">
                                <span>PPH TERUTANG</span>
                                <span id="prev-pph">0</span>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 3. NERACA (BALANCE SHEET) -->
            <section id="section-neraca" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                <div class="flex justify-between items-center mb-4 border-b pb-2">
                    <h3 class="font-bold text-gray-700 flex items-center gap-2">
                        <span>3. NERACA (Kesehatan keuangan)</span>
                        <span id="balance-indicator" class="text-xs font-bold px-3 py-1 rounded-full bg-gray-100 text-gray-500">Belum seimbang</span>
                    </h3>
                    <label class="flex items-center gap-2 cursor-pointer bg-blue-50 px-3 py-1.5 rounded-lg hover:bg-blue-100 transition border border-blue-200">
                        <input type="checkbox" id="auto-balance-toggle" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500" onchange="runEngine()">
                        <span class="text-xs font-bold text-blue-700">Isi Otomatis (Estimasi)</span>
                    </label>
                </div>
                
                <!-- CATATAN REKOMENDASI NERACA -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6 rounded-r-lg">
                    <div class="flex items-start">
                        <i data-lucide="info" class="w-5 h-5 text-blue-500 mr-2 mt-0.5"></i>
                        <div>
                            <p class="text-xs font-bold text-blue-800 mb-1">Strategi planning Keuangan</p>
                            <p class="text-xs text-blue-700 leading-relaxed" id="neraca-recommendation">
                                Menunggu data...
                            </p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Sisi Aset -->
                    <div class="space-y-4">
                        <h4 class="text-sm font-bold text-blue-800 uppercase border-b border-blue-100 pb-1">A. Aset / Harta</h4>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Kas & Setara Kas</label>
                            <input type="text" id="inp-kas" class="currency-input w-full p-2 border rounded text-right bg-white" placeholder="0" onkeyup="formatAndReact(this)">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Piutang & Persediaan (Aset Lancar Lain)</label>
                            <input type="text" id="inp-aset-lancar-lain" class="currency-input w-full p-2 border rounded text-right bg-white" placeholder="0" onkeyup="formatAndReact(this)">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Aset Tetap (Tanah, Bangunan, Mesin)</label>
                            <input type="text" id="inp-aset-tetap" class="currency-input w-full p-2 border rounded text-right bg-white" placeholder="0" onkeyup="formatAndReact(this)">
                        </div>
                        <div class="bg-blue-50 p-2 rounded flex justify-between items-center">
                            <span class="text-xs font-bold text-blue-800">Total Aset</span>
                            <span id="val-total-aset" class="font-mono font-bold text-blue-900">0</span>
                        </div>
                    </div>

                    <!-- Sisi Passiva -->
                    <div class="space-y-4">
                        <h4 class="text-sm font-bold text-red-800 uppercase border-b border-red-100 pb-1">B. Kewajiban & Ekuitas</h4>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Utang Jangka Pendek & Panjang</label>
                            <input type="text" id="inp-liabilitas" class="currency-input w-full p-2 border rounded text-right bg-white" placeholder="0" onkeyup="formatAndReact(this)">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Modal Saham / Modal Disetor</label>
                            <input type="text" id="inp-modal" class="currency-input w-full p-2 border rounded text-right bg-white" placeholder="0" onkeyup="formatAndReact(this)">
                        </div>
                        
                        <div class="opacity-70">
                            <label class="block text-xs text-green-600 mb-1 font-bold">Laba Tahun Berjalan (Otomatis)</label>
                            <input type="text" id="inp-laba-berjalan" class="currency-input w-full p-2 border border-green-200 bg-green-50 rounded text-right font-bold text-green-800" readonly value="0">
                        </div>
                        <div class="bg-red-50 p-2 rounded flex justify-between items-center">
                            <span class="text-xs font-bold text-red-800">Total Passiva</span>
                            <span id="val-total-passiva" class="font-mono font-bold text-red-900">0</span>
                        </div>
                    </div>
                </div>
            </section>

            <!-- 4. KREDIT PAJAK -->
            <section id="section-4" class="bg-white p-6 rounded-xl shadow-sm border border-gray-200">
                <h3 class="font-bold text-gray-700 mb-4 border-b pb-2">4. Kredit Yg pernah bayar/diPotong jd pengurang pjk</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Bupot PPh 22/23 (L3)</label>
                        <input type="text" id="inp-l3" class="currency-input w-full p-3 border rounded text-right" placeholder="0" onkeyup="formatAndReact(this)">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Angsuran PPh 25 (L6)</label>
                        <input type="text" id="inp-l6" class="currency-input w-full p-3 border rounded text-right" placeholder="0" onkeyup="formatAndReact(this)">
                    </div>
                </div>
            </section>

            <!-- 5. FINAL SUMMARY -->
            <section id="section-summary" class="bg-slate-800 text-white p-6 rounded-xl shadow-lg border border-slate-700">
                <div class="flex justify-between items-end mb-6">
                    <h3 class="font-bold text-xl flex items-center gap-2">
                        <i data-lucide="file-check" class="text-yellow-400"></i> Ringkasan Induk
                    </h3>
                    <div class="text-right">
                        <p class="text-[10px] text-slate-400 uppercase">Status Akhir</p>
                        <h2 class="text-2xl font-extrabold" id="out-status">NIHIL</h2>
                    </div>
                </div>

                <div class="space-y-2 font-mono text-sm border-t border-slate-700 pt-4">
                    <div class="flex justify-between"><span>PKP / DPP</span> <span id="out-pkp">0</span></div>
                    <div class="flex justify-between"><span>PPh Terutang</span> <span id="out-tax">0</span></div>
                    <div class="flex justify-between text-green-400"><span>Total Kredit</span> <span id="out-credit">0</span></div>
                    <div class="flex justify-between text-xl font-bold pt-2 border-t border-slate-600 mt-2">
                        <span>Nilai Akhir</span> <span id="out-balance">Rp 0</span>
                    </div>
                </div>
            </section>

        </div>
    </main>

    <script>
        lucide.createIcons();

        // CONSTANTS
        const LIMIT_UMKM = 4800000000;
        const LIMIT_31E = 50000000000;
        const CURRENT_YEAR = 2025;

        // UTILS
        const parse = (val) => parseFloat(val?.replace(/\./g, '').replace(/,/g, '') || 0);
        const format = (num) => num.toLocaleString('id-ID');

        function formatAndReact(el) {
            let original = el.value.replace(/\D/g, '');
            if(original) el.value = parseInt(original).toLocaleString('id-ID');
            runEngine();
        }

        function runEngine() {
            // 1. DATA GATHERING
            const entity = document.getElementById('inp-entity').value;
            const regYear = parseInt(document.getElementById('inp-reg-year').value || 0);
            const omzet = parse(document.getElementById('inp-omzet').value);
            
            const hpp = parse(document.getElementById('inp-hpp').value);
            const gaji = parse(document.getElementById('inp-gaji').value); 
            const biaya = parse(document.getElementById('inp-biaya').value);
            const korPos = parse(document.getElementById('inp-kor-pos').value);
            const korNeg = parse(document.getElementById('inp-kor-neg').value);

            // 2. PROFIT CALCULATION
            const totalBiaya = gaji + biaya;
            const labaKotor = omzet - hpp;
            const labaKomersial = labaKotor - totalBiaya;
            const labaFiskal = labaKomersial + korPos - korNeg;
            
            document.getElementById('badge-laba').innerText = "Netto Komersial: Rp " + format(labaKomersial);
            document.getElementById('inp-laba-berjalan').value = format(labaKomersial);

            // Update Preview Laporan Laba Rugi
            document.getElementById('prev-omzet').innerText = format(omzet);
            document.getElementById('prev-hpp').innerText = "(" + format(hpp) + ")";
            document.getElementById('prev-laba-kotor').innerText = format(labaKotor);
            document.getElementById('prev-gaji').innerText = "(" + format(gaji) + ")";
            document.getElementById('prev-biaya').innerText = "(" + format(biaya) + ")";
            document.getElementById('prev-laba-bersih').innerText = format(labaKomersial);
            
            // 3. SCHEME VALIDATION
            let limitYears = (entity === 'PT') ? 3 : 4;
            let startYear = Math.max(regYear, 2018);
            let runningYear = (CURRENT_YEAR - startYear) + 1;
            let canUseUMKM = (runningYear <= limitYears) && (omzet <= LIMIT_UMKM);

            const cardUmkm = document.getElementById('card-umkm');
            const radioUmkm = document.querySelector('input[value="UMKM"]');
            const radioUmum = document.querySelector('input[value="UMUM"]');
            const blocker = document.getElementById('blocker-umkm');

            if (!canUseUMKM) {
                blocker.classList.remove('hidden');
                radioUmkm.disabled = true;
                if(radioUmkm.checked) radioUmum.checked = true;
            } else {
                blocker.classList.add('hidden');
                radioUmkm.disabled = false;
            }

            // 4. TAX CALCULATION & PREVIEW UPDATE
            const scheme = document.querySelector('input[name="scheme"]:checked').value;
            let tax = 0;
            let pkp = 0;
            let dpp = 0;
            let schemeLabel = "";
            let tarifDisplay = "";

            if (scheme === 'UMKM') {
                tax = Math.floor(omzet * 0.005);
                schemeLabel = "Final 0,5%";
                dpp = omzet;
                tarifDisplay = "0,5% (Final PP 55)";
            } else {
                pkp = (labaFiskal > 0) ? Math.floor(labaFiskal/1000)*1000 : 0;
                dpp = pkp;
                
                if (pkp <= 0) {
                    tax = 0;
                    tarifDisplay = "Nihil (Rugi)";
                    schemeLabel = "Tarif Umum";
                }
                else if (omzet <= 4800000000) {
                    // Fasilitas 31E Full (11% on PKP)
                    tax = Math.floor(pkp * 0.11);
                    tarifDisplay = "Pasal 31E 11%";
                    schemeLabel = "Pasal 31E 11%";
                }
                else if (omzet > 50000000000) {
                    tax = Math.floor(pkp * 0.22);
                    tarifDisplay = "Tarif Normal 22%";
                    schemeLabel = "Tarif Normal 22%";
                }
                else {
                    let pkpFas = (4800000000 / omzet) * pkp;
                    if(pkpFas > pkp) pkpFas = pkp;
                    let pkpNon = pkp - pkpFas;
                    tax = Math.floor((pkpFas * 0.11) + (pkpNon * 0.22));
                    tarifDisplay = "Fasilitas Pasal 31E & 17";
                    schemeLabel = "Fasilitas Pasal 31E & 17";
                }
            }

            document.getElementById('prev-dpp').innerText = format(dpp);
            document.getElementById('prev-tarif').innerText = tarifDisplay;
            document.getElementById('prev-pph').innerText = format(tax);

            // 5. NERACA AUTO-BALANCE LOGIC (SMART HEURISTIC)
            const autoBalance = document.getElementById('auto-balance-toggle').checked;
            const recBox = document.getElementById('neraca-recommendation');
            
            // Elements
            const inpKas = document.getElementById('inp-kas');
            const inpAsetLancar = document.getElementById('inp-aset-lancar-lain');
            const inpAsetTetap = document.getElementById('inp-aset-tetap');
            const inpLiabilitas = document.getElementById('inp-liabilitas');
            const inpModal = document.getElementById('inp-modal');
            
            // --- HEURISTIC CALCULATION (Always runs to generate advice) ---
            // 1. Est. Modal = 5% Omzet (Min 5jt)
            let estModal = Math.max(omzet * 0.05, 5000000);
            // 2. Est. Utang = 2% Omzet
            let estUtang = omzet * 0.02;
            
            // Total Pasiva
            let totalPasiva = estUtang + estModal + labaKomersial;
            let recText = "";

            // Handle Negative Equity case
            if (totalPasiva < 0) {
                let deficit = Math.abs(totalPasiva) + 10000000;
                estModal += deficit;
                totalPasiva = estUtang + estModal + labaKomersial;
                recText = `Rugi fiskal terdeteksi. Modal Saham perlu minimal Rp ${format(estModal)} agar Neraca positif. `;
            } else {
                recText = `Rekomendasi Angka: Modal Rp ${format(estModal)} (5% Omzet) dan Utang Rp ${format(estUtang)}. `;
            }

            // Distribute Assets (Conservative)
            let estAsetTetap = omzet * 0.05; 
            let estAsetLancar = omzet * 0.05; 
            let estKas = totalPasiva - estAsetTetap - estAsetLancar;

            // If Kas negative, adjust
            if (estKas < 0) {
                let shortfall = Math.abs(estKas) + 1000000;
                estKas = 1000000; 
                estModal += shortfall;
                recText += `Kekurangan aset dialihkan ke Modal (+Rp ${format(shortfall)}). `;
            } else {
                recText += `Sisa penyeimbang Rp ${format(estKas)} dimasukkan ke Kas.`;
            }

            // Update Recommendation Text UI
            recBox.innerText = recText;

            // Apply Auto-Fill only if Toggle is ON
            if (autoBalance) {
                inpKas.value = format(estKas);
                inpAsetLancar.value = format(estAsetLancar);
                inpAsetTetap.value = format(estAsetTetap);
                inpLiabilitas.value = format(estUtang);
                inpModal.value = format(estModal);
                
                // Visual Indicator for Auto Mode
                [inpKas, inpAsetLancar, inpAsetTetap, inpLiabilitas, inpModal].forEach(el => {
                    el.classList.add('bg-blue-50', 'text-blue-800');
                });
            } else {
                // Clear Visual Indicator for Manual Mode
                [inpKas, inpAsetLancar, inpAsetTetap, inpLiabilitas, inpModal].forEach(el => {
                    el.classList.remove('bg-blue-50', 'text-blue-800');
                });
            }

            // Calculate Balance based on current values (Auto or Manual)
            const kas = parse(inpKas.value);
            const asetLancar = parse(inpAsetLancar.value);
            const asetTetap = parse(inpAsetTetap.value);
            const totalAset = kas + asetLancar + asetTetap;

            const liabilitas = parse(inpLiabilitas.value);
            const modal = parse(inpModal.value);
            const totalPassiva = liabilitas + modal + labaKomersial;

            const neracaDiff = totalAset - totalPassiva;
            const isBalanced = Math.abs(neracaDiff) < 100;

            document.getElementById('val-total-aset').innerText = format(totalAset);
            document.getElementById('val-total-passiva').innerText = format(totalPassiva);
            document.getElementById('mon-aset').innerText = format(totalAset);
            document.getElementById('mon-passiva').innerText = format(totalPassiva);

            const badgeBal = document.getElementById('balance-badge');
            const indBal = document.getElementById('balance-indicator');
            const sectionNeraca = document.getElementById('section-neraca');

            if (isBalanced && totalAset > 0) {
                badgeBal.className = "mt-2 w-full py-1 text-center rounded text-xs font-bold bg-green-600 text-white";
                badgeBal.innerText = "BALANCED";
                indBal.innerText = "OK";
                indBal.className = "ml-auto text-xs font-bold px-3 py-1 rounded-full bg-green-100 text-green-700";
                sectionNeraca.className = "bg-white p-6 rounded-xl shadow-sm border-2 balance-ok";
            } else if (totalAset === 0) {
                badgeBal.innerText = "Belum Diisi";
                badgeBal.className = "mt-2 w-full py-1 text-center rounded text-xs font-bold bg-gray-600 text-gray-300";
                sectionNeraca.className = "bg-white p-6 rounded-xl shadow-sm border border-gray-200";
            } else {
                badgeBal.className = "mt-2 w-full py-1 text-center rounded text-xs font-bold bg-red-600 text-white";
                badgeBal.innerText = `SELISIH: ${format(neracaDiff)}`;
                indBal.innerText = "Belum seimbang";
                indBal.className = "ml-auto text-xs font-bold px-3 py-1 rounded-full bg-red-100 text-red-700";
                sectionNeraca.className = "bg-white p-6 rounded-xl shadow-sm border-2 balance-err";
            }

            // 6. FINAL SUMMARY
            const l3 = parse(document.getElementById('inp-l3').value);
            const l6 = parse(document.getElementById('inp-l6').value);
            const credit = l3 + l6;
            const balance = tax - credit;

            document.getElementById('live-tax').innerText = "Rp " + format(tax);
            document.getElementById('active-scheme-label').innerText = schemeLabel;
            
            document.getElementById('out-pkp').innerText = format(pkp);
            document.getElementById('out-tax').innerText = format(tax);
            document.getElementById('out-credit').innerText = format(credit);
            
            const elBal = document.getElementById('out-balance');
            const elStat = document.getElementById('out-status');
            elBal.innerText = "Rp " + format(Math.abs(balance));

            if(balance > 0) { elStat.innerText = "KURANG BAYAR"; elStat.className = "text-2xl font-extrabold text-red-400"; }
            else if(balance < 0) { elStat.innerText = "LEBIH BAYAR"; elStat.className = "text-2xl font-extrabold text-yellow-400"; }
            else { elStat.innerText = "NIHIL"; elStat.className = "text-2xl font-extrabold text-gray-400"; }
        }

        function downloadCSV() {
            const badgeText = document.getElementById('balance-badge').innerText;
            if (badgeText.includes("SELISIH")) {
                alert("PERINGATAN: Neraca tidak seimbang. Mohon perbaiki data aset/kewajiban sebelum download.");
                return;
            }
            alert("Generating CSV L1 (Laba Rugi & Neraca), Induk, dan Kredit Pajak...");
        }

        runEngine();
    </script>
</body>
</html>
