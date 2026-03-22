<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Friends Wishes - XML Version</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;1,700&family=Quicksand:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Quicksand', sans-serif;
            background-color: #f8f8f8;
            background-image: url('https://www.transparenttextures.com/patterns/felt.png');
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
    </style>
</head>

<body class="min-h-screen flex flex-col items-center p-6 sm:p-12">

    <div class="max-w-md w-full bg-white/50 p-8 rounded-lg shadow-sm">
        <h1 class="text-5xl text-center header-title mb-10">Friends Wishes</h1>

        <!-- Form Input -->
        <form id="wishForm" action="<?php echo site_url('wishes/submit'); ?>" method="POST" class="space-y-4 mb-12">

            <div id="csrf_container">
                <?php if (config_item('csrf_protection') === TRUE): ?>
                    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>" id="csrf_token">
                <?php endif; ?>
            </div>

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
                <span id="count"><?php echo (isset($wishes)) ? count($wishes) : 0; ?></span> BEST FRIENDS WISHES
            </h2>

            <div id="wishesList" class="mt-6">
                <?php if (!empty($wishes)): ?>
                    <?php
                    $counter = 0;
                    foreach ($wishes as $row):
                        $counter++;
                        // Konversi eksplisit ke string dan gunakan fallback '' untuk PHP 8.1+, XML Menggunakan operator panah ->
                        $name = (string)($row->name ?? '');
                        $message = (string)($row->message ?? '');
                        $date = (string)($row->created_at ?? '');
                    ?>
                        <div class="wish-item <?php echo ($counter > 5) ? 'hidden-wish' : ''; ?>">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="font-bold text-orange-400"><?php echo htmlspecialchars($name); ?></span>
                                <span class="text-xs text-gray-400">
                                    <?php echo ($date) ? date('d M Y, H:i', strtotime($date)) : '-'; ?>
                                </span>
                            </div>
                            <p class="text-gray-700 break-words"><?php echo nl2br(htmlspecialchars($message)); ?></p>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div id="empty-state" class="text-center py-4 text-gray-400 italic">Belum ada ucapan.</div>
                <?php endif; ?>
            </div>

            <div id="loadMoreContainer" class="mt-4 flex justify-center <?php echo (isset($wishes) && count($wishes) > 5) ? '' : 'hidden'; ?>">
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
            $(document).on('click', '#loadMoreBtn', function() {
                const $hiddenItems = $('.hidden-wish');
                $hiddenItems.slice(0, 5).fadeIn(800).removeClass('hidden-wish');
                if ($('.hidden-wish').length === 0) {
                    $('#loadMoreContainer').fadeOut(400);
                }
            });

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
                                    <span class="font-bold text-orange-400">${name}</span>
                                    <span class="text-xs text-gray-400">${time}</span>
                                </div>
                                <p class="text-gray-700 break-words">${message.replace(/\n/g, '<br>')}</p>
                            </div>
                        `;

                            $wishesList.prepend(newWishHtml);
                            $wishesList.find('.wish-item').first().fadeIn(800);

                            const $countElem = $('#count');
                            $countElem.text(parseInt($countElem.text()) + 1);

                            $('#message').val('');
                            $('#name').val('');
                        } else {
                            alert('Gagal: ' + (response.message || 'Terjadi kesalahan'));
                        }
                    },
                    error: function(xhr) {
                        alert('Gagal menghubungi server.');
                    },
                    complete: function() {
                        $btn.prop('disabled', false).text(originalBtnText);
                    }
                });
                return false;
            });
        });
    </script>
</body>

</html>