<?php
// Koneksi HUBUNGAN nabi adam
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_tutorial";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Tambah atau Update Data
if (isset($_POST['action']) && $_POST['action'] == 'simpan') {
    $judul = $conn->real_escape_string($_POST['judul']);
    $konten = $conn->real_escape_string($_POST['konten']); 
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if (!empty($judul) && !empty($konten)) {
        if ($id > 0) {
            $conn->query("UPDATE tutorials SET judul = '$judul', konten = '$konten' WHERE id = $id");
            header("Location: index.php?id=$id&pesan=diupdate");
        } else {
            $conn->query("INSERT INTO tutorials (judul, konten) VALUES ('$judul', '$konten')");
            header("Location: index.php?pesan=berhasil");
        }
        exit();
    }
}

// Hapus Data
if (isset($_GET['action']) && $_GET['action'] == 'hapus' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $conn->query("DELETE FROM tutorials WHERE id = $id");
    header("Location: index.php?pesan=dihapus");
    exit();
}

// Ambil Detail Tutorial
$detail = null;
$isEditMode = false;
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $result = $conn->query("SELECT * FROM tutorials WHERE id = $id");
    $detail = $result->fetch_assoc();
    if (isset($_GET['action']) && $_GET['action'] == 'edit') {
        $isEditMode = true;
    }
}

// Ambil Daftar Semua Tutorial
$daftar_tutorial = $conn->query("SELECT id, judul, created_at FROM tutorials ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocuBook Pro - Repositori Panduan Digital</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }

        .flipbook-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 15px 0;
            background: #cbd5e1; 
            border-radius: 20px;
            box-shadow: inset 0 4px 10px rgba(0,0,0,0.08);
        }
        @media (min-width: 768px) {
            .flipbook-container { padding: 30px 0; }
        }

        /* LEMBARAN HALAMAN BUKU */
        .my-page {
            background-color: #fcfbf9; 
            background-image: linear-gradient(to right, rgba(0,0,0,0.012) 0%, rgba(255,255,255,0) 5%, rgba(255,255,255,0) 95%, rgba(0,0,0,0.02) 100%);
            padding: 20px;
            box-sizing: border-box;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow-y: auto;
        }
        @media (min-width: 768px) {
            .my-page { padding: 40px; }
        }

        /* COVER DEPAN NAVY */
        .my-page[data-density="hard"] {
            background: linear-gradient(135deg, #1C2E42 0%, #2D3E50 100%);
        }

        /* Desain Gambar Screenshot */
        .guide-content img {
            max-width: 100%;
            height: auto;
            max-height: 200px;
            object-fit: contain;
            border-radius: 10px;
            margin: 10px auto 4px auto; 
            border: 1px solid #e2e8f0; 
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.04);
            display: block;
        }
        @media (min-width: 768px) {
            .guide-content img { max-height: 250px; }
        }

        /* Header Langkah Premium */
        .custom-step-badge {
            display: inline-flex;
            align-items: center;
            background: #b45309;
            color: white;
            font-weight: 700;
            font-size: 0.75rem;
            padding: 4px 10px;
            border-radius: 6px;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .custom-step-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 10px;
            line-height: 1.4;
        }
        @media (min-width: 768px) {
            .custom-step-title { font-size: 1.2rem; }
        }

        /* Penjelasan Sub-Langkah (Tanda Minus) */
        .sub-step-info {
            background-color: #f0fdf4;
            border-left: 4px solid #16a34a;
            padding: 8px 12px;
            margin: 6px 0;
            border-radius: 0 8px 8px 0;
            font-size: 0.85rem;
            color: #14532d;
            font-weight: 500;
        }
        @media (min-width: 768px) {
            .sub-step-info { font-size: 0.9rem; }
        }

        /* Dark Terminal Command Box */
        .command-block {
            font-family: 'Fira Code', monospace;
            background-color: #1e293b;
            padding: 10px 12px;
            border-radius: 8px;
            margin: 6px 0; 
            font-size: 0.8rem;
            border-left: 4px solid #38bdf8;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            position: relative;
            z-index: 50; 
        }
        @media (min-width: 768px) {
            .command-block { padding: 12px 14px; font-size: 0.85rem; }
        }

        .command-text, .command-text *, .command-text span {
            color: #f8fafc !important;
            word-break: break-all;
        }

        /* Tombol Salin Premium */
        .copy-btn {
            color: #94a3b8;
            cursor: pointer;
            transition: color 0.15s;
            padding: 4px 8px;
            font-size: 1rem;
            border-radius: 6px;
        }
        .copy-btn:hover { 
            color: #f8fafc; 
            background-color: rgba(255,255,255,0.1);
        }

        .step-body-text p, .step-body-text div {
            margin-top: 2px !important;
            margin-bottom: 2px !important;
        }

        .guide-content p, .guide-content div { 
            font-size: 0.9rem; 
            line-height: 1.5; 
            color: #334155; 
        }
        @media (min-width: 768px) {
            .guide-content p, .guide-content div { font-size: 0.95rem; }
        }

        .my-page::-webkit-scrollbar { width: 4px; }
        .my-page::-webkit-scrollbar-thumb { background-color: #e2e8f0; border-radius: 10px; }
        .editor:empty:before { content: attr(placeholder); color: #9ca3af; font-style: italic; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">

    <header class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="index.php" class="flex items-center space-x-3">
                <div class="bg-amber-700 text-white p-2 rounded-xl shadow-lg"><i class="fa-solid fa-book text-lg"></i></div>
                <span class="text-xl font-bold text-slate-900 tracking-tight">Docu<span class="text-amber-700">Book</span></span>
            </a>
            <a href="index.php" class="inline-flex items-center px-3 py-1.5 md:px-4 md:py-2 text-xs md:text-sm font-semibold rounded-xl text-white bg-amber-700 hover:bg-amber-800 transition">
                <i class="fa-solid fa-plus mr-1.5"></i> Baru
            </a>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 py-4 md:py-6 flex-1 w-full flex flex-col lg:flex-row gap-6">
        
        <aside class="w-full lg:w-64 flex-shrink-0">
            <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-sm">
                <div class="mb-3">
                    <div class="relative">
                        <input type="text" id="searchBar" onkeyup="filterTutorials()" placeholder="Cari di rak buku..." class="w-full text-sm rounded-xl border border-slate-200 p-2 pl-9 outline-none focus:border-amber-700 transition">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-3.5 text-slate-400 text-sm"></i>
                    </div>
                </div>
                <nav class="space-y-1 max-h-[200px] lg:max-h-[400px] overflow-y-auto pr-1" id="tutorialList">
                    <?php while($row = $daftar_tutorial->fetch_assoc()): 
                        $isActive = (isset($_GET['id']) && $_GET['id'] == $row['id'] && !$isEditMode) ? 'bg-amber-50 text-amber-900 font-semibold border-amber-700' : 'text-slate-600 hover:bg-slate-50 border-transparent';
                    ?>
                        <a href="index.php?id=<?= $row['id']; ?>" class="tutorial-item flex items-center py-2.5 px-3 text-sm rounded-xl border-l-4 transition <?= $isActive; ?>">
                            <span class="truncate"><i class="fa-solid fa-bookmark mr-2 text-amber-600/60"></i><?= htmlspecialchars($row['judul']); ?></span>
                        </a>
                    <?php endwhile; ?>
                </nav>
            </div>
        </aside>

        <main class="flex-1 flex flex-col">
            
            <?php if ($detail && !$isEditMode): ?>
                <div class="w-full flex flex-col items-center">
                    
                    <div class="w-full max-w-[1060px] bg-white border border-slate-200 rounded-2xl p-4 mb-4 flex flex-col sm:flex-row justify-between items-center gap-3 shadow-sm">
                        <div class="text-xs text-slate-400 text-center sm:text-left">
                            <i class="fa-solid fa-circle-check text-emerald-600 mr-1"></i> Mode auto-responsif aktif untuk kenyamanan HP & PC.
                        </div>
                        <div class="flex items-center space-x-2 w-full sm:w-auto justify-center">
                            <a href="index.php?action=edit&id=<?= $detail['id']; ?>" class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-xl text-xs font-semibold shadow transition"><i class="fa-solid fa-pen-to-square mr-1"></i> Edit</a>
                            <button onclick="exportToWord()" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-xl text-xs font-semibold shadow transition"><i class="fa-solid fa-file-word mr-1"></i> Word</button>
                            <a href="index.php?action=hapus&id=<?= $detail['id']; ?>" onclick="return confirm('Hapus buku panduan ini?');" class="bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 px-3 py-1.5 rounded-xl text-xs font-semibold transition"><i class="fa-solid fa-trash mr-1"></i> Hapus</a>
                        </div>
                    </div>

                    <div class="flipbook-container w-full max-w-[1060px] overflow-hidden">
                        <div id="book" class="mx-auto shadow-2xl">
                            
                            <div class="my-page flex flex-col justify-between text-white p-6 md:p-10 select-none md:border-r md:border-slate-900/40" data-density="hard">
                                <div class="flex flex-col items-center justify-between h-full text-center">
                                    <div>
                                        <div class="flex items-center justify-center space-x-4 mb-6 md:mb-10 text-amber-400/80">
                                            <i class="fa-solid fa-terminal text-3xl md:text-4xl"></i>
                                            <i class="fa-brands fa-docker text-3xl md:text-4xl"></i>
                                        </div>
                                        <div class="bg-amber-400/10 text-amber-400 font-extrabold text-[10px] px-3 py-1.5 rounded-full inline-flex items-center space-x-1.5 mb-5 border border-amber-500/20">
                                            <i class="fa-solid fa-file-invoice text-xs"></i>
                                            <span>Sistem Digital Documentation</span>
                                        </div>
                                        <h1 id="docTitle" class="text-2xl md:text-4xl font-sans font-black text-white leading-tight tracking-tighter mb-6 md:text-shadow"><?= htmlspecialchars($detail['judul']); ?></h1>
                                    </div>
                                    <div class="border-t border-white/10 pt-4 w-full">
                                        <div class="bg-white/5 border border-white/10 rounded-lg p-2.5 inline-block">
                                            <p class="text-[10px] text-amber-300/80">Pengarsipan Digital:</p>
                                            <p class="text-xs font-medium text-white mt-0.5"><?= date('d M Y', strtotime($detail['created_at'])); ?></p>
                                        </div>
                                        <p class="text-[10px] font-semibold text-amber-400 mt-6 uppercase tracking-wider animate-pulse flex items-center justify-center space-x-2">
                                            <span>Geser untuk Membaca</span>
                                            <i class="fa-solid fa-book-open"></i>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <?php 
                                $konten_mentah = $detail['konten'];
                                $konten_mentah = preg_replace('/<p>&nbsp;<\/p>/i', '', $konten_mentah);
                                $konten_mentah = preg_replace('/<p><br><\/p>/i', '', $konten_mentah);

                                $chunks = preg_split('/(?=(?:\d+\.|\b[1-9]\d*\.))/', $konten_mentah);
                                $chunks = array_filter(array_map('trim', $chunks));
                                
                                $p_num = 2;
                                
                                foreach($chunks as $chunk) {
                                    if(!empty($chunk)) {
                                        if (preg_match('/^(\d+)\.\s*(.*?)(?=<br>|<p>|<div>|<img>|$)/is', $chunk, $matches)) {
                                            $num_clean = $matches[1];
                                            $title_text = strip_tags($matches[2]);
                                            
                                            $pattern_remove = '/^' . $num_clean . '\.\s*' . preg_quote($matches[2], '/') . '/is';
                                            $body_content = preg_replace($pattern_remove, '', $chunk, 1);
                                            
                                            $header_komponen = '
                                                <div class="mb-2 select-none">
                                                    <span class="custom-step-badge">Langkah ' . $num_clean . '</span>
                                                    <h2 class="custom-step-title">' . htmlspecialchars($title_text) . '</h2>
                                                </div>';
                                        } else {
                                            $header_komponen = '';
                                            $body_content = $chunk;
                                        }

                                        $saved_images = [];
                                        $body_content = preg_replace_callback('/<img[^>]+>/i', function($img_matches) use (&$saved_images) {
                                            $placeholder = "___IMG_PLACEHOLDER_" . count($saved_images) . "___";
                                            $saved_images[$placeholder] = $img_matches[0];
                                            return $placeholder;
                                        }, $body_content);

                                        $body_content = preg_replace_callback('/(?:&quot;|"|“|”|&ldquo;|&rdquo;)([^"“\”]+?)(?:&quot;|"|“|”|&ldquo;|&rdquo;)/i', function($matches_code) {
                                            $clean_code = trim(htmlspecialchars_decode(strip_tags($matches_code[1])));
                                            if(empty($clean_code) || strlen($clean_code) < 2) return $matches_code[0];
                                            return '
                                            <div class="command-block" onmousedown="event.stopPropagation();" onclick="event.stopPropagation();">
                                                <span class="command-text"><i class="fa-solid fa-chevron-right mr-2 text-sky-400"></i>' . htmlspecialchars($clean_code) . '</span>
                                                <i class="fa-regular fa-copy copy-btn" onmousedown="event.stopPropagation();" onclick="copyCommand(event, this, \'' . addslashes($clean_code) . '\')" title="Salin Kode"></i>
                                            </div>';
                                        }, $body_content);

                                        $body_content = preg_replace('/(?:<br\s*\/?>|\n|^)\s*-\s*([^<\n]+)/i', '<div class="sub-step-info"><i class="fa-solid fa-circle-info mr-1.5 text-emerald-600"></i> $1</div>', $body_content);

                                        foreach ($saved_images as $placeholder => $original_img_tag) {
                                            $body_content = str_replace($placeholder, $original_img_tag, $body_content);
                                        }

                                        echo '<div class="my-page bg-white">';
                                        echo '  <div class="guide-content w-full">';
                                        echo '     ' . $header_komponen;
                                        echo '     <div class="step-body-text">' . $body_content . '</div>';
                                        echo '  </div>';
                                        echo '  <div class="text-right text-[10px] text-slate-400 tracking-wider select-none border-t border-slate-100 pt-2 w-full mt-4">HALAMAN ' . $p_num . '</div>';
                                        echo '</div>';
                                        $p_num++;
                                    }
                                }

                                if($p_num % 2 != 0) {
                                    echo '<div class="my-page bg-slate-50 flex flex-col justify-between">';
                                    echo '  <div class="text-center my-auto text-slate-300 italic text-xs">Akhir Dokumen Panduan.</div>';
                                    echo '  <div class="text-right text-[10px] text-slate-400 tracking-wider border-t border-slate-100 pt-2 select-none">HALAMAN '.$p_num.'</div>';
                                    echo '</div>';
                                    $p_num++;
                                }
                            ?>

                            <div class="my-page flex items-center justify-center text-white" data-density="hard">
                                <div class="text-center opacity-30 text-amber-300 select-none">
                                    <i class="fa-solid fa-book-open text-3xl mb-2"></i>
                                    <p class="text-[10px] font-sans tracking-widest uppercase">DocuBook Engine v6</p>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="flex justify-center items-center space-x-4 mt-6">
                        <button id="btnPrev" class="bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 px-4 py-2 rounded-xl text-xs font-semibold shadow-sm transition"><i class="fa-solid fa-chevron-left mr-2"></i> Sblm</button>
                        <span id="pageState" class="text-xs font-bold text-slate-500 bg-slate-200/60 px-3 py-1.5 rounded-lg select-none">Memuat...</span>
                        <button id="btnNext" class="bg-white hover:bg-slate-100 text-slate-700 border border-slate-300 px-4 py-2 rounded-xl text-xs font-semibold shadow-sm transition">Slnjt <i class="fa-solid fa-chevron-right ml-2"></i></button>
                    </div>

                </div>
                
                <div id="docContent" class="hidden"><?= $detail['konten']; ?></div>

                <script>
                    document.addEventListener("DOMContentLoaded", function() {
                        // Deteksi ukuran layar awal untuk menentukan orientasi buku
                        const isMobile = window.innerWidth < 768;

                        const pageFlip = new St.PageFlip(document.getElementById("book"), {
                            width: isMobile ? window.innerWidth - 40 : 520,  
                            height: isMobile ? 550 : 660, 
                            size: "fixed",
                            minWidth: 280,
                            minHeight: 350,
                            maxWidth: 1200,
                            maxHeight: 900,
                            drawShadow: true,
                            showCover: !isMobile, // Matikan efek cover tebal ganda di HP
                            usePortrait: isMobile, // Paksa satu halaman saja di HP
                            swipeDistance: 30 
                        });

                        pageFlip.loadFromHTML(document.querySelectorAll(".my-page"));

                        const pageState = document.getElementById("pageState");
                        const updatePageState = () => {
                            pageState.innerText = `Hal: ${pageFlip.getCurrentPageIndex() + 1} / ${pageFlip.getPageCount()}`;
                        };
                        
                        updatePageState();
                        pageFlip.on('flip', updatePageState);

                        document.getElementById("btnPrev").addEventListener("click", () => pageFlip.flipPrev());
                        document.getElementById("btnNext").addEventListener("click", () => pageFlip.flipNext());
                        
                        // Handle perubahan orientasi layar atau resize browser
                        window.addEventListener('resize', () => {
                            location.reload();
                        });
                    });

                    function copyCommand(e, btn, text) {
                        e.preventDefault();
                        e.stopPropagation(); 
                        
                        navigator.clipboard.writeText(text).then(() => {
                            btn.className = "fa-solid fa-check text-emerald-400";
                            setTimeout(() => {
                                btn.className = "fa-regular fa-copy copy-btn";
                            }, 2000);
                        }).catch(err => {
                            console.error('Gagal menyalin teks: ', err);
                        });
                    }
                </script>

            <?php else: ?>
                <div class="w-full bg-white border border-slate-200 rounded-2xl p-4 md:p-6 shadow-sm">
                    <h2 class="text-xl font-bold text-slate-900 mb-1"><?= $isEditMode ? '⚙️ Edit Buku Panduan' : '📚 Tulis Buku Panduan Baru' ?></h2>
                    <p class="text-sm text-slate-400 mb-5">Gunakan tanda minus (-) untuk sub-langkah hijau, dan tanda petik ganda ("") untuk memicu box terminal premium.</p>
                    
                    <form action="index.php" method="POST" id="formTutorial" class="space-y-4">
                        <input type="hidden" name="action" value="simpan">
                        <input type="hidden" name="id" value="<?= $isEditMode ? $detail['id'] : 0; ?>">
                        <input type="hidden" name="konten" id="hiddenKonten">
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Judul Dokumentasi</label>
                            <input type="text" name="judul" value="<?= $isEditMode ? htmlspecialchars($detail['judul']) : ''; ?>" class="w-full rounded-xl border border-slate-200 px-4 py-2.5 text-slate-900 outline-none focus:border-amber-700 font-medium transition" placeholder="Contoh: Tutorial Setup Database Docker" required>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Isi Panduan</label>
                            <div class="bg-slate-50 border border-b-0 border-slate-200 rounded-t-xl px-4 py-2 flex items-center space-x-2">
                                <button type="button" onclick="formatDoc('bold')" class="p-1.5 text-slate-600 hover:bg-slate-200 rounded transition" title="Tebal"><i class="fa-solid fa-bold"></i></button>
                                <button type="button" onclick="formatDoc('italic')" class="p-1.5 text-slate-600 hover:bg-slate-200 rounded transition" title="Miring"><i class="fa-solid fa-italic"></i></button>
                            </div>
                            <div class="editor w-full min-h-[320px] border border-slate-200 rounded-b-xl p-4 bg-white focus:border-amber-700 outline-none overflow-y-auto max-h-[500px] text-slate-800" contenteditable="true" id="editor" placeholder="Contoh:&#10;1. Perintah Update&#10;- Jalankan perintah berikut&#10;&quot;sudo apt update&quot;"><?= $isEditMode ? $detail['konten'] : ''; ?></div>
                        </div>
                        
                        <div class="flex items-center space-x-2">
                            <button type="submit" class="inline-flex justify-center items-center px-6 py-3 font-semibold rounded-xl text-white bg-amber-700 hover:bg-amber-800 shadow transition">
                                <i class="fa-solid fa-feather-pointed mr-2"></i> Simpan Buku
                            </button>
                            <?php if($isEditMode): ?>
                                <a href="index.php?id=<?= $detail['id']; ?>" class="inline-flex justify-center items-center px-5 py-3 font-semibold rounded-xl text-slate-700 bg-slate-100 hover:bg-slate-200 transition">Batal</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

        </main>
    </div>

    <script>
        function formatDoc(cmd, value = null) {
            document.execCommand(cmd, false, value);
            document.getElementById('editor').focus();
        }

        const form = document.getElementById('formTutorial');
        if(form) {
            form.addEventListener('submit', function() {
                document.getElementById('hiddenKonten').value = document.getElementById('editor').innerHTML;
            });
        }

        function filterTutorials() {
            const input = document.getElementById('searchBar').value.toLowerCase();
            const items = document.getElementsByClassName('tutorial-item');
            for (let i = 0; i < items.length; i++) {
                let title = items[i].innerText || items[i].textContent;
                items[i].style.display = title.toLowerCase().includes(input) ? "" : "none";
            }
        }

        function exportToWord() {
            const judul = document.getElementById('docTitle').innerText;
            const kontenHtml = document.getElementById('docContent').innerHTML;
            const htmlSource = `
                <html xmlns:o='urn:schemas-microsoft-com:office:office' xmlns:w='urn:schemas-microsoft-com:office:word' xmlns='http://www.w3.org/TR/REC-html40'>
                <head><title>${judul}</title>
                <style>
                    body { font-family: 'Arial', sans-serif; line-height: 1.6; }
                    h1 { font-size: 24pt; font-weight: bold; margin-bottom: 12pt; }
                    h2 { font-size: 16pt; font-weight: bold; margin-top: 18pt; color: #78350f; border-bottom: 1px solid #ccc; }
                    p { font-size: 11pt; }
                    img { max-width: 100%; margin: 12pt 0; display: block; }
                </style>
                </head>
                <body><h1>${judul}</h1><hr><div>${kontenHtml}</div></body></html>`;

            const blob = new Blob(['\ufeff' + htmlSource], { type: 'application/msword' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = judul.replace(/[^a-zA-Z0-9]/g, "_") + '.doc';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }
    </script>
</body>
</html>