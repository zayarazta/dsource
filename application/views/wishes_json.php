<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends Wishes - CI3 JSON Version</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;1,700&family=Quicksand:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Quicksand', sans-serif;
            background-color: #f8f8f8;
            background-image: url('https://www.transparenttextures.com/patterns/felt.png');
            user-select: none;
            -webkit-user-select: none;
        }

        .header-title {
            font-family: 'Playfair Display', serif;
            color: #4a5568;
        }

        .input-shadow {
            box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);
        }

        .wish-item {
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 1rem;
            margin-bottom: 1rem;
        }

        .hidden-wish {
            display: none;
        }

        /* Gaya Countdown */
        .countdown-item {
            background-color: #b3bc9f;
            color: white;
            border-radius: 12px;
            min-width: 75px;
            padding: 10px 5px;
            text-align: center;
        }

        .countdown-val {
            font-size: 1.8rem;
            font-weight: 500;
            display: block;
            line-height: 1;
        }

        .countdown-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
    </style>
</head>

<body class="min-h-screen flex flex-col items-center p-6 sm:p-12">

    <div class="max-w-md w-full bg-white/50 p-8 rounded-lg shadow-sm border border-white">

        <!-- Bagian Countdown -->
        <div class="mb-12">
            <h2 class="text-center text-gray-400 tracking-[0.4em] font-light mb-6 uppercase text-sm">Save The Date</h2>
            <div id="countdown" class="flex justify-center gap-2 sm:gap-3">
                <div class="countdown-item">
                    <span id="days" class="countdown-val">00</span>
                    <span class="countdown-label">Days</span>
                </div>
                <div class="countdown-item">
                    <span id="hours" class="countdown-val">00</span>
                    <span class="countdown-label">Hours</span>
                </div>
                <div class="countdown-item">
                    <span id="minutes" class="countdown-val">00</span>
                    <span class="countdown-label">Mins</span>
                </div>
                <div class="countdown-item">
                    <span id="seconds" class="countdown-val">00</span>
                    <span class="countdown-label">Secs</span>
                </div>
            </div>
        </div>

        <h1 class="text-5xl text-center header-title mb-10">Friends Wishes</h1>

        <!-- Form Input -->
        <!-- Action diarahkan kembali ke Controller CI3 Anda -->
        <form id="wishForm" action="<?php echo site_url('wishes/submit'); ?>" method="POST" class="space-y-4 mb-12">

            <?php if (config_item('csrf_protection') === TRUE): ?>
                <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" id="csrf_token">
            <?php endif; ?>

            <textarea
                name="message"
                id="message"
                placeholder="Ucapan Selamat & doa"
                class="w-full h-24 p-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-200 input-shadow resize-none"
                required></textarea>

            <div class="relative">
                <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" />
                    </svg>
                </span>
                <input
                    name="name"
                    id="name"
                    type="text"
                    placeholder="Nama Anda"
                    class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-200 input-shadow"
                    required>
            </div>

            <div class="flex justify-end">
                <button
                    type="submit"
                    id="submitBtn"
                    class="px-8 py-2 border border-orange-300 text-orange-600 rounded hover:bg-orange-50 transition-colors disabled:opacity-50">
                    Kirim
                </button>
            </div>
        </form>

        <!-- Daftar Ucapan -->
        <div class="mt-8">
            <h2 class="text-gray-600 font-bold tracking-widest text-sm uppercase mb-4 border-b-2 border-gray-800 inline-block pb-1">
                <span id="count"><?php echo count($wishes ?? []); ?></span> BEST FRIENDS WISHES
            </h2>

            <div id="wishesList" class="mt-6">
                <?php if (!empty($wishes)): ?>
                    <?php
                    $counter = 0;
                    foreach ($wishes as $row):
                        $counter++;
                        $name = $row['name'] ?? '';
                        $message = $row['message'] ?? '';
                        $date = $row['created_at'] ?? '';
                    ?>
                        <div class="wish-item <?php echo ($counter > 5) ? 'hidden-wish' : ''; ?>">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-bold text-orange-400"><?php echo htmlspecialchars((string)$name); ?></span>
                                <span class="text-xs text-gray-400">
                                    <?php echo ($date) ? date('d M Y, H:i', strtotime((string)$date)) : '-'; ?>
                                </span>
                            </div>
                            <p class="text-gray-700 break-words"><?php echo nl2br(htmlspecialchars((string)$message)); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div id="empty-state" class="text-center py-4 text-gray-400 italic">Belum ada ucapan.</div>
                <?php endif; ?>
            </div>

            <div id="loadMoreContainer" class="mt-4 flex justify-center <?php echo (count($wishes ?? []) > 5) ? '' : 'hidden'; ?>">
                <button
                    id="loadMoreBtn"
                    class="px-6 py-2 border border-orange-300 text-orange-600 rounded hover:bg-orange-50 transition-colors text-sm">
                    Muat Lebih Banyak
                </button>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // --- LOGIKA COUNTDOWN ---
            const targetDate = new Date("March 30, 2026 09:00:00").getTime();

            const updateCountdown = setInterval(function() {
                const now = new Date().getTime();
                const distance = targetDate - now;

                if (distance < 0) {
                    clearInterval(updateCountdown);
                    document.getElementById("countdown").innerHTML = "<div class='text-orange-500 font-bold tracking-wider'>ACARA SEDANG BERLANGSUNG</div>";
                    return;
                }

                const d = Math.floor(distance / (1000 * 60 * 60 * 24));
                const h = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const m = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const s = Math.floor((distance % (1000 * 60)) / 1000);

                $("#days").text(d.toString().padStart(2, '0'));
                $("#hours").text(h.toString().padStart(2, '0'));
                $("#minutes").text(m.toString().padStart(2, '0'));
                $("#seconds").text(s.toString().padStart(2, '0'));
            }, 1000);

            // Disable klik kanan
            // $(document).on('contextmenu', function(e) {
            //     e.preventDefault();
            //     return false;
            // });

            // Load More
            $(document).on('click', '#loadMoreBtn', function() {
                const $hiddenItems = $('.hidden-wish');
                $hiddenItems.slice(0, 5).fadeIn(800).removeClass('hidden-wish');
                if ($('.hidden-wish').length === 0) $('#loadMoreContainer').fadeOut(400);
            });

            // Submit Form AJAX
            $('#wishForm').on('submit', function(e) {
                e.preventDefault();
                const $form = $(this);
                const $btn = $('#submitBtn');
                const $wishesList = $('#wishesList');
                const originalBtnText = $btn.text();

                $btn.prop('disabled', true).text('Mengirim...');

                $.ajax({
                    url: $form.attr('action'),
                    type: 'POST',
                    data: $form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        // Update CSRF token jika diaktifkan di CI3
                        if (response.csrf_name && response.csrf_hash) {
                            $('input[name="' + response.csrf_name + '"]').val(response.csrf_hash);
                        }

                        if (response.status === 'success') {
                            $('#empty-state').remove();

                            const name = response.data.name || 'Anonim';
                            const message = response.data.message || '';
                            const time = response.data.time || 'Baru saja';

                            const newWishHtml = `
                            <div class="wish-item" style="display:none;">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold text-orange-400">${escapeHtml(name)}</span>
                                    <span class="text-xs text-gray-400">${time}</span>
                                </div>
                                <p class="text-gray-700 break-words">${escapeHtml(message).replace(/\n/g, '<br>')}</p>
                            </div>
                        `;

                            $wishesList.prepend(newWishHtml);
                            $wishesList.find('.wish-item').first().fadeIn(800);

                            const $countElem = $('#count');
                            $countElem.text(parseInt($countElem.text()) + 1);

                            $('#message').val('');
                            $('#name').val('');
                        } else {
                            alert('Gagal: ' + (response.message || 'Terjadi kesalahan server'));
                        }
                    },
                    error: function(xhr) {
                        alert('Gagal menghubungi server. Pastikan folder data memiliki izin tulis (writable).');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text(originalBtnText);
                    }
                });
                return false;
            });

            function escapeHtml(text) {
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, function(m) {
                    return map[m];
                });
            }
        });
    </script>
</body>

</html>