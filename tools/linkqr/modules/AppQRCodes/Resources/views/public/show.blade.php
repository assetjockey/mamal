<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $qrCode->name }}</title>
    @foreach (($pixelSnippets ?? []) as $pixelSnippet)
        {!! $pixelSnippet !!}
    @endforeach
    <style>
        :root {
            color-scheme: light;
            font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            --primary: #2563eb;
            --accent: #14b8a6;
            --ink: #0f172a;
            --muted: #52647a;
            --line: #dbe5f0;
            --soft: #f6f9fd;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background:
                radial-gradient(circle at 12% 0%, color-mix(in srgb, var(--primary) 15%, transparent), transparent 34%),
                radial-gradient(circle at 92% 12%, color-mix(in srgb, var(--accent) 18%, transparent), transparent 32%),
                linear-gradient(180deg, #f8fbff 0%, #eef4f8 100%);
            color: var(--ink);
        }
        a { color: inherit; }
        .wrap { max-width: 1060px; margin: 0 auto; padding: 28px 18px 48px; }
        .shell {
            overflow: hidden;
            border: 1px solid var(--line);
            border-radius: 30px;
            background: rgba(255,255,255,.94);
            box-shadow: 0 30px 90px -55px rgba(15,23,42,.55);
        }
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 20px;
            border-bottom: 1px solid var(--line);
            background: linear-gradient(135deg, rgba(255,255,255,.84), color-mix(in srgb, var(--primary) 7%, white));
        }
        .brand-mini { display: flex; min-width: 0; align-items: center; gap: 12px; }
        .mark {
            display: inline-flex;
            height: 48px;
            width: 48px;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border-radius: 16px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #fff;
            font-size: 21px;
            font-weight: 900;
            box-shadow: 0 14px 34px -24px rgba(15,23,42,.7);
        }
        .mark img { height: 100%; width: 100%; object-fit: cover; }
        .label {
            color: var(--primary);
            font-size: 11px;
            font-weight: 850;
            letter-spacing: .16em;
            text-transform: uppercase;
        }
        .topbar .label { display: block; margin-bottom: 2px; }
        .topbar-title { margin: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 15px; font-weight: 850; }
        .hero {
            display: grid;
            grid-template-columns: minmax(0, 1.08fr) minmax(300px, .92fr);
            gap: 0;
        }
        .main-copy { padding: 36px clamp(22px, 5vw, 52px); }
        .eyebrow { margin: 0 0 14px; color: var(--primary); font-size: 12px; font-weight: 850; letter-spacing: .16em; text-transform: uppercase; }
        h1 { margin: 0; font-size: clamp(34px, 6vw, 58px); line-height: .98; letter-spacing: -.055em; }
        .lead { max-width: 62ch; margin: 18px 0 0; color: var(--muted); font-size: 16px; line-height: 1.75; }
        .actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 26px; }
        .btn {
            display: inline-flex;
            min-height: 48px;
            align-items: center;
            justify-content: center;
            border-radius: 15px;
            padding: 0 18px;
            background: var(--primary);
            color: #fff;
            font-weight: 850;
            text-decoration: none;
            box-shadow: 0 18px 38px -28px color-mix(in srgb, var(--primary) 80%, #111827);
        }
        .btn.secondary { border: 1px solid var(--line); background: #fff; color: var(--ink); box-shadow: none; }
        .side {
            border-left: 1px solid var(--line);
            padding: 20px;
            background: linear-gradient(145deg, color-mix(in srgb, var(--primary) 7%, white), color-mix(in srgb, var(--accent) 8%, white));
        }
        .media {
            overflow: hidden;
            border: 1px solid rgba(255,255,255,.78);
            border-radius: 24px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            min-height: 220px;
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.24);
        }
        .media.placeholder {
            display: flex;
            min-height: 220px;
            align-items: flex-end;
            padding: 18px;
            color: #fff;
        }
        .preview-card {
            width: 100%;
            border: 1px solid rgba(255,255,255,.38);
            border-radius: 20px;
            padding: 16px;
            background: rgba(15,23,42,.22);
            backdrop-filter: blur(14px);
        }
        .preview-card p { margin: 0; color: rgba(255,255,255,.82); }
        .preview-card .preview-title { margin-top: 6px; color: #fff; font-size: 20px; font-weight: 850; line-height: 1.18; }
        .preview-card .preview-meta { margin-top: 12px; display: flex; gap: 8px; flex-wrap: wrap; }
        .preview-chip { border-radius: 999px; background: rgba(255,255,255,.18); padding: 6px 9px; color: #fff; font-size: 11px; font-weight: 800; }
        .media img { display: block; width: 100%; height: 260px; object-fit: cover; }
        .details {
            display: grid;
            gap: 10px;
            margin-top: 12px;
        }
        .info {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            border: 1px solid rgba(219,229,240,.9);
            border-radius: 17px;
            padding: 13px;
            background: rgba(255,255,255,.82);
            text-decoration: none;
        }
        .info-key {
            display: inline-flex;
            height: 34px;
            width: 34px;
            flex: 0 0 auto;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: color-mix(in srgb, var(--primary) 11%, white);
            color: var(--primary);
            font-size: 12px;
            font-weight: 900;
        }
        .info p { margin: 0; }
        .info .k { color: #64748b; font-size: 11px; font-weight: 850; letter-spacing: .12em; text-transform: uppercase; }
        .info .v { margin-top: 3px; color: var(--ink); font-size: 14px; font-weight: 760; line-height: 1.45; overflow-wrap: anywhere; white-space: pre-line; }
        .sections {
            display: grid;
            gap: 18px;
            padding: 0 clamp(18px, 4vw, 36px) 34px;
        }
        .section-card {
            border: 1px solid var(--line);
            border-radius: 24px;
            padding: 20px;
            background: #fff;
        }
        .section-head { display: flex; justify-content: space-between; gap: 16px; align-items: flex-end; margin-bottom: 16px; }
        .section-head h2 { margin: 4px 0 0; font-size: 21px; letter-spacing: -.03em; }
        .grid { display: grid; gap: 12px; }
        .products { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .product {
            display: grid;
            grid-template-columns: 86px minmax(0,1fr);
            gap: 13px;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 12px;
            background: var(--soft);
        }
        .product img { width: 86px; height: 86px; border-radius: 15px; object-fit: cover; background: #e2e8f0; }
        .product-title { margin: 0; color: var(--ink); font-weight: 850; }
        .product-desc { margin: 5px 0 0; color: var(--muted); font-size: 13px; line-height: 1.55; }
        .price { display: inline-flex; margin-top: 9px; color: var(--primary); font-weight: 900; }
        .link-list { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .link-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border: 1px solid #e2e8f0;
            border-radius: 17px;
            padding: 14px 15px;
            background: var(--soft);
            color: var(--ink);
            font-weight: 820;
            text-decoration: none;
        }
        .link-item span:last-child { color: var(--primary); font-size: 12px; letter-spacing: .1em; text-transform: uppercase; }
        .form-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .field-preview {
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 13px 14px;
            background: var(--soft);
            color: #64748b;
            font-size: 14px;
            font-weight: 760;
        }
        body.template-mobile_stack {
            background:
                radial-gradient(circle at 50% -10%, color-mix(in srgb, var(--primary) 24%, transparent), transparent 42%),
                linear-gradient(180deg, #eef6ff 0%, #f8fbff 100%);
        }
        .template-mobile_stack .wrap { max-width: 520px; padding-top: 20px; }
        .template-mobile_stack .shell { border-radius: 34px; }
        .template-mobile_stack .topbar { border-bottom: 0; background: #fff; }
        .template-mobile_stack .hero { display: block; }
        .template-mobile_stack .main-copy { padding: 28px 24px; text-align: center; }
        .template-mobile_stack .main-copy .eyebrow { display: none; }
        .template-mobile_stack h1 { font-size: clamp(34px, 10vw, 48px); }
        .template-mobile_stack .lead { margin-left: auto; margin-right: auto; }
        .template-mobile_stack .actions { justify-content: center; }
        .template-mobile_stack .side { border-left: 0; border-top: 1px solid var(--line); background: #f8fbff; }
        .template-mobile_stack .media { min-height: 170px; }
        .template-mobile_stack .media img { height: 210px; }
        .template-mobile_stack .details { grid-template-columns: 1fr; }
        .template-mobile_stack .sections { padding: 0 18px 26px; }
        .template-mobile_stack .products, .template-mobile_stack .link-list, .template-mobile_stack .form-grid { grid-template-columns: 1fr; }

        .template-catalog_grid .hero { grid-template-columns: minmax(0,.86fr) minmax(360px,1.14fr); }
        .template-catalog_grid .main-copy { padding-bottom: 24px; }
        .template-catalog_grid .sections { padding-top: 28px; }
        .template-catalog_grid .section-card { border-radius: 28px; background: linear-gradient(180deg, #fff, #f8fbff); }
        .template-catalog_grid .products { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .template-catalog_grid .product { display: block; padding: 14px; background: #fff; }
        .template-catalog_grid .product img,
        .template-catalog_grid .product > div[style*="width:86px"] { width: 100% !important; height: 150px !important; margin-bottom: 12px; }
        .template-catalog_grid .actions .btn.secondary { min-height: 40px; }

        .template-editorial .wrap { max-width: 900px; }
        .template-editorial .shell { border-radius: 18px; box-shadow: 0 24px 75px -58px rgba(15,23,42,.42); }
        .template-editorial .topbar { background: #fff; }
        .template-editorial .hero { display: block; }
        .template-editorial .main-copy { padding: 52px clamp(24px, 7vw, 76px) 34px; }
        .template-editorial h1 { max-width: 13ch; font-size: clamp(42px, 8vw, 72px); }
        .template-editorial .side { border-left: 0; border-top: 1px solid var(--line); background: #fbfdff; }
        .template-editorial .side { display: grid; grid-template-columns: minmax(0,.95fr) minmax(0,1.05fr); gap: 16px; }
        .template-editorial .details { margin-top: 0; }
        .template-editorial .section-card { border-radius: 18px; box-shadow: none; }

        .template-compact_banner .wrap { max-width: 980px; }
        .template-compact_banner .hero { display: block; }
        .template-compact_banner .main-copy { padding: 34px clamp(22px, 6vw, 64px); }
        .template-compact_banner h1 { max-width: 14ch; font-size: clamp(36px, 7vw, 64px); }
        .template-compact_banner .side { border-left: 0; border-top: 1px solid var(--line); display: grid; grid-template-columns: minmax(0,1fr) minmax(0,1fr); gap: 14px; }

        .template-sidebar_profile .hero { grid-template-columns: minmax(280px,.42fr) minmax(0,.58fr); }
        .template-sidebar_profile .main-copy { order: 2; }
        .template-sidebar_profile .side { order: 1; border-left: 0; border-right: 1px solid var(--line); }
        .template-sidebar_profile .media { min-height: 180px; }

        .template-menu_board .shell { background: #111827; color: #fff; }
        .template-menu_board .topbar, .template-menu_board .side, .template-menu_board .section-card { border-color: rgba(255,255,255,.12); background: #172033; }
        .template-menu_board h1, .template-menu_board .topbar-title, .template-menu_board .section-head h2, .template-menu_board .product-title { color: #fff; }
        .template-menu_board .lead, .template-menu_board .product-desc, .template-menu_board .info .k { color: #cbd5e1; }
        .template-menu_board .product, .template-menu_board .info { border-color: rgba(255,255,255,.12); background: rgba(255,255,255,.06); }
        .template-menu_board .price { color: #facc15; }

        .template-lead_capture .hero { grid-template-columns: minmax(0,.78fr) minmax(320px,1.22fr); }
        .template-lead_capture .main-copy { background: linear-gradient(135deg, color-mix(in srgb, var(--primary) 9%, white), #fff); }
        .template-lead_capture .btn { min-width: 170px; }
        .template-lead_capture .field-preview { background: #fff; box-shadow: 0 12px 28px -24px rgba(15,23,42,.35); }

        .template-event_pass .shell { border-radius: 36px; }
        .template-event_pass .topbar { background: linear-gradient(135deg, color-mix(in srgb, var(--primary) 18%, white), #fff); }
        .template-event_pass .media, .template-event_pass .section-card { border-radius: 30px; }
        .template-event_pass .btn { border-radius: 999px; }

        .template-file_card .wrap { max-width: 820px; }
        .template-file_card .hero { display: block; }
        .template-file_card .side { border-left: 0; border-top: 1px solid var(--line); background: #fff; }
        .template-file_card .media { min-height: 140px; }
        .template-file_card .section-card, .template-file_card .info { border-radius: 14px; }

        .template-review_spotlight body, body.template-review_spotlight { background: linear-gradient(180deg, #fffbeb, #f8fafc); }
        .template-review_spotlight .shell { border-color: #fde68a; }
        .template-review_spotlight .media { background: linear-gradient(135deg, #f59e0b, var(--primary)); }
        .template-review_spotlight .label, .template-review_spotlight .price { color: #d97706; }

        .template-resume_sheet .wrap { max-width: 920px; }
        .template-resume_sheet .shell { border-radius: 14px; background: #fff; }
        .template-resume_sheet .topbar { background: #f8fafc; }
        .template-resume_sheet .hero { grid-template-columns: minmax(0,1fr) 320px; }
        .template-resume_sheet .section-card, .template-resume_sheet .info { border-radius: 12px; }

        .template-booking_card .wrap { max-width: 760px; }
        .template-booking_card .hero { display: block; }
        .template-booking_card .main-copy { text-align: center; }
        .template-booking_card .actions { justify-content: center; }
        .template-booking_card .side { border-left: 0; border-top: 1px solid var(--line); }
        .template-booking_card .details { grid-template-columns: repeat(2, minmax(0,1fr)); }

        .template-app_download .shell { background: #0f172a; color: #fff; }
        .template-app_download .topbar, .template-app_download .side, .template-app_download .section-card { border-color: rgba(255,255,255,.12); background: rgba(255,255,255,.06); }
        .template-app_download h1, .template-app_download .topbar-title, .template-app_download .section-head h2 { color: #fff; }
        .template-app_download .lead { color: #cbd5e1; }
        .template-app_download .btn.secondary { color: #0f172a; }

        .template-payment_card .wrap { max-width: 680px; }
        .template-payment_card .hero { display: block; }
        .template-payment_card .main-copy { text-align: center; background: linear-gradient(135deg, color-mix(in srgb, var(--accent) 12%, white), white); }
        .template-payment_card .actions { justify-content: center; }
        .template-payment_card .side { border-left: 0; border-top: 1px solid var(--line); }

        .template-link_hub .wrap { max-width: 620px; }
        .template-link_hub .hero { display: block; }
        .template-link_hub .main-copy { text-align: center; }
        .template-link_hub .side { display: none; }
        .template-link_hub .link-list { grid-template-columns: 1fr; }
        .template-link_hub .link-item { min-height: 58px; border-radius: 999px; background: #fff; }

        /*
         * Public template personalities.
         * These rules intentionally change layout, density, shape, and surface treatment
         * so customers are choosing real page directions, not small color variants.
         */
        body.template-split_showcase .wrap { max-width: 1080px; padding-top: 36px; }
        body.template-split_showcase .shell { border-radius: 28px; }
        body.template-split_showcase .hero { min-height: 360px; }
        body.template-split_showcase .main-copy { display: flex; flex-direction: column; justify-content: center; }
        body.template-split_showcase .side { display: flex; flex-direction: column; justify-content: center; }

        body.template-mobile_stack {
            background: linear-gradient(180deg, #101827 0%, #dbeafe 52%, #f8fafc 100%);
        }
        body.template-mobile_stack .wrap { max-width: 430px; padding-top: 28px; }
        body.template-mobile_stack .shell {
            border: 10px solid #0f172a;
            border-radius: 44px;
            background: #f8fafc;
            box-shadow: 0 34px 90px -42px rgba(15,23,42,.85);
        }
        body.template-mobile_stack .topbar {
            justify-content: center;
            padding: 22px 20px 10px;
            border: 0;
            background: transparent;
        }
        body.template-mobile_stack .brand-mini { flex-direction: column; text-align: center; }
        body.template-mobile_stack .mark { border-radius: 999px; }
        body.template-mobile_stack .hero { display: flex; flex-direction: column; }
        body.template-mobile_stack .main-copy { padding: 20px 26px 26px; text-align: center; }
        body.template-mobile_stack .side { padding: 0 18px 22px; border: 0; background: transparent; }
        body.template-mobile_stack .media { min-height: 150px; border-radius: 26px; }
        body.template-mobile_stack .details { gap: 8px; }
        body.template-mobile_stack .info,
        body.template-mobile_stack .section-card,
        body.template-mobile_stack .link-item { border-radius: 22px; }
        body.template-mobile_stack .sections { padding: 0 18px 20px; }

        body.template-catalog_grid {
            background: linear-gradient(180deg, #f8fafc 0%, #eef6ff 100%);
        }
        body.template-catalog_grid .wrap { max-width: 1180px; }
        body.template-catalog_grid .shell { border-radius: 18px; background: #fff; }
        body.template-catalog_grid .topbar { border-bottom: 0; background: #fff; }
        body.template-catalog_grid .hero { grid-template-columns: .72fr 1.28fr; border-top: 1px solid var(--line); }
        body.template-catalog_grid .main-copy { padding: 40px 42px; }
        body.template-catalog_grid h1 { font-size: clamp(36px, 5vw, 62px); }
        body.template-catalog_grid .side { background: #f1f8fb; }
        body.template-catalog_grid .sections { padding: 26px; }
        body.template-catalog_grid .section-card { border-radius: 18px; padding: 24px; }
        body.template-catalog_grid .products { grid-template-columns: repeat(3, minmax(0,1fr)); }
        body.template-catalog_grid .product {
            display: flex;
            min-height: 100%;
            flex-direction: column;
            border-radius: 16px;
            background: #fff;
        }
        body.template-catalog_grid .product img,
        body.template-catalog_grid .product > div[style*="width:86px"] {
            width: 100% !important;
            height: 170px !important;
            margin-bottom: 12px;
            border-radius: 14px;
        }

        body.template-editorial {
            background: #f4f1ea;
        }
        body.template-editorial .wrap { max-width: 860px; padding-top: 44px; }
        body.template-editorial .shell {
            border-color: #d7cec0;
            border-radius: 4px;
            background: #fffdf8;
            box-shadow: 0 28px 70px -50px rgba(73,55,35,.6);
        }
        body.template-editorial .topbar { padding: 24px 34px; background: #fffdf8; }
        body.template-editorial .hero { display: block; border-top: 1px solid #e7dfd2; }
        body.template-editorial .main-copy { padding: 54px clamp(28px, 7vw, 78px); }
        body.template-editorial .eyebrow,
        body.template-editorial .label { color: #9a3412; }
        body.template-editorial h1 { max-width: 11ch; font-size: clamp(46px, 8vw, 86px); letter-spacing: -.07em; }
        body.template-editorial .side {
            display: grid;
            grid-template-columns: minmax(0,1fr) minmax(0,1fr);
            gap: 18px;
            padding: 28px 34px;
            border-left: 0;
            border-top: 1px solid #e7dfd2;
            background: #faf6ee;
        }
        body.template-editorial .media,
        body.template-editorial .info,
        body.template-editorial .section-card { border-radius: 4px; }

        body.template-compact_banner .wrap { max-width: 1040px; padding-top: 58px; }
        body.template-compact_banner .shell { border-radius: 999px 28px 28px 999px; }
        body.template-compact_banner .topbar { display: none; }
        body.template-compact_banner .hero {
            display: grid;
            grid-template-columns: 1fr 340px;
            align-items: stretch;
            min-height: 250px;
        }
        body.template-compact_banner .main-copy { padding: 34px 46px 36px 58px; }
        body.template-compact_banner h1 { font-size: clamp(36px, 5vw, 62px); }
        body.template-compact_banner .side {
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-top: 0;
            background: linear-gradient(145deg, color-mix(in srgb, var(--primary) 16%, white), color-mix(in srgb, var(--accent) 18%, white));
        }
        body.template-compact_banner .media { min-height: 140px; }
        body.template-compact_banner .details { grid-template-columns: 1fr; }

        body.template-sidebar_profile {
            background: #101827;
            color: #f8fafc;
        }
        body.template-sidebar_profile .wrap { max-width: 1020px; padding-top: 34px; }
        body.template-sidebar_profile .shell {
            border-color: rgba(148,163,184,.28);
            border-radius: 0;
            background: #172033;
            box-shadow: 0 26px 85px -55px #000;
        }
        body.template-sidebar_profile .topbar { background: #0f172a; border-color: rgba(148,163,184,.24); }
        body.template-sidebar_profile .hero { grid-template-columns: 320px minmax(0,1fr); }
        body.template-sidebar_profile .side {
            order: 1;
            padding: 26px;
            border-right: 1px solid rgba(148,163,184,.24);
            border-left: 0;
            background: #0f172a;
        }
        body.template-sidebar_profile .main-copy { order: 2; padding: 52px; }
        body.template-sidebar_profile h1,
        body.template-sidebar_profile .topbar-title,
        body.template-sidebar_profile .section-head h2 { color: #fff; }
        body.template-sidebar_profile .lead,
        body.template-sidebar_profile .info .k,
        body.template-sidebar_profile .product-desc { color: #b6c8dd; }
        body.template-sidebar_profile .info .v,
        body.template-sidebar_profile .product-title,
        body.template-sidebar_profile .link-item span:first-child { color: #fff; }
        body.template-sidebar_profile .info,
        body.template-sidebar_profile .section-card,
        body.template-sidebar_profile .product,
        body.template-sidebar_profile .link-item {
            border-color: rgba(148,163,184,.22);
            background: rgba(255,255,255,.05);
            color: #fff;
        }

        body.template-menu_board {
            background: #0b1220;
        }
        body.template-menu_board .wrap { max-width: 960px; }
        body.template-menu_board .shell {
            border: 8px solid #1f2937;
            border-radius: 18px;
            background: #111827;
        }
        body.template-menu_board .topbar { background: #111827; }
        body.template-menu_board .hero {
            display: block;
            border-top: 1px solid rgba(255,255,255,.12);
        }
        body.template-menu_board .main-copy { padding: 42px 42px 24px; text-align: center; }
        body.template-menu_board .actions { justify-content: center; }
        body.template-menu_board .side { display: none; }
        body.template-menu_board .sections { padding: 20px 34px 34px; }
        body.template-menu_board .products,
        body.template-menu_board .link-list { grid-template-columns: 1fr; }
        body.template-menu_board .product,
        body.template-menu_board .link-item {
            display: grid;
            grid-template-columns: 88px 1fr auto;
            align-items: center;
            border-radius: 0;
            border-width: 0 0 1px;
            background: transparent;
        }
        body.template-menu_board .info .k { color: #cbd5e1; }
        body.template-menu_board .info .v,
        body.template-menu_board .product-title,
        body.template-menu_board .link-item span:first-child { color: #fff; }

        body.template-lead_capture {
            background: linear-gradient(135deg, #eef2ff, #ecfeff);
        }
        body.template-lead_capture .wrap { max-width: 1000px; }
        body.template-lead_capture .shell { border-radius: 34px; }
        body.template-lead_capture .hero { grid-template-columns: .9fr 1.1fr; }
        body.template-lead_capture .main-copy {
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: linear-gradient(160deg, color-mix(in srgb, var(--primary) 12%, white), #fff);
        }
        body.template-lead_capture .side {
            border-left: 1px solid var(--line);
            background: #fff;
        }
        body.template-lead_capture .sections { padding-top: 0; }
        body.template-lead_capture .section-card {
            border-radius: 26px;
            background: color-mix(in srgb, var(--primary) 5%, white);
        }
        body.template-lead_capture .form-grid { grid-template-columns: 1fr; }
        body.template-lead_capture .field-preview { min-height: 54px; background: #fff; }

        body.template-event_pass {
            background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 54%, #f8fafc 54%);
        }
        body.template-event_pass .wrap { max-width: 780px; padding-top: 46px; }
        body.template-event_pass .shell {
            position: relative;
            border: 0;
            border-radius: 28px;
            background: #fff;
            box-shadow: 0 36px 90px -48px rgba(15,23,42,.9);
        }
        body.template-event_pass .shell::before,
        body.template-event_pass .shell::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 34px;
            height: 34px;
            border-radius: 999px;
            background: #1d4ed8;
            transform: translateY(-50%);
        }
        body.template-event_pass .shell::before { left: -17px; }
        body.template-event_pass .shell::after { right: -17px; }
        body.template-event_pass .topbar { background: #fff; border-bottom: 1px dashed #cbd5e1; }
        body.template-event_pass .hero { display: block; }
        body.template-event_pass .main-copy { padding: 46px 48px 28px; text-align: center; }
        body.template-event_pass .actions { justify-content: center; }
        body.template-event_pass .side { border-left: 0; border-top: 1px dashed #cbd5e1; background: #f8fafc; }

        body.template-file_card {
            background: #e5e7eb;
        }
        body.template-file_card .wrap { max-width: 760px; padding-top: 38px; }
        body.template-file_card .shell {
            border: 1px solid #cbd5e1;
            border-radius: 2px;
            background: #fff;
            box-shadow: 0 30px 70px -55px rgba(15,23,42,.65);
        }
        body.template-file_card .topbar { background: #fff; }
        body.template-file_card .hero { display: block; }
        body.template-file_card .main-copy { padding: 56px 64px 28px; }
        body.template-file_card .side { border-left: 0; border-top: 1px solid #e2e8f0; background: #fff; }
        body.template-file_card .media,
        body.template-file_card .info,
        body.template-file_card .section-card { border-radius: 2px; }

        body.template-review_spotlight {
            background: radial-gradient(circle at top, #fef3c7, #fff7ed 46%, #f8fafc 100%);
        }
        body.template-review_spotlight .wrap { max-width: 860px; padding-top: 44px; }
        body.template-review_spotlight .shell {
            border: 0;
            border-radius: 32px;
            background: #fff;
            box-shadow: 0 34px 90px -50px rgba(146,64,14,.55);
        }
        body.template-review_spotlight .topbar { background: #fff7ed; }
        body.template-review_spotlight .hero { display: block; }
        body.template-review_spotlight .main-copy { padding: 56px 44px 30px; text-align: center; }
        body.template-review_spotlight .main-copy::before {
            content: "*****";
            display: block;
            margin-bottom: 18px;
            color: #f59e0b;
            font-size: 26px;
            letter-spacing: .08em;
        }
        body.template-review_spotlight .actions { justify-content: center; }
        body.template-review_spotlight .side { border-left: 0; border-top: 1px solid #fde68a; background: #fffbeb; }

        body.template-resume_sheet {
            background: #e2e8f0;
        }
        body.template-resume_sheet .wrap { max-width: 920px; padding-top: 36px; }
        body.template-resume_sheet .shell {
            border-radius: 0;
            background: #fff;
            box-shadow: 0 28px 70px -50px rgba(15,23,42,.65);
        }
        body.template-resume_sheet .topbar { background: #0f172a; color: #fff; }
        body.template-resume_sheet .topbar .label,
        body.template-resume_sheet .topbar-title { color: #fff; }
        body.template-resume_sheet .hero { grid-template-columns: minmax(0,1fr) 300px; }
        body.template-resume_sheet .main-copy { padding: 54px; }
        body.template-resume_sheet .side { background: #f8fafc; }
        body.template-resume_sheet .media,
        body.template-resume_sheet .info,
        body.template-resume_sheet .section-card { border-radius: 0; }

        body.template-booking_card {
            background: linear-gradient(180deg, #f0f9ff, #f8fafc);
        }
        body.template-booking_card .wrap { max-width: 720px; padding-top: 44px; }
        body.template-booking_card .shell {
            border-radius: 34px;
            background: #fff;
            box-shadow: 0 34px 90px -50px rgba(2,132,199,.45);
        }
        body.template-booking_card .topbar {
            justify-content: center;
            border-bottom: 0;
            background: transparent;
            text-align: center;
        }
        body.template-booking_card .brand-mini { flex-direction: column; }
        body.template-booking_card .hero { display: block; }
        body.template-booking_card .main-copy { padding: 24px 42px 34px; text-align: center; }
        body.template-booking_card h1 { max-width: none; font-size: clamp(38px, 7vw, 64px); }
        body.template-booking_card .actions { justify-content: center; }
        body.template-booking_card .side { padding: 0 28px 30px; border: 0; background: transparent; }
        body.template-booking_card .media { min-height: 150px; }
        body.template-booking_card .details { grid-template-columns: repeat(2, minmax(0,1fr)); }
        body.template-booking_card .info { background: #f8fafc; }

        body.template-app_download {
            background: radial-gradient(circle at 50% 0%, color-mix(in srgb, var(--primary) 36%, transparent), transparent 38%), #070b16;
        }
        body.template-app_download .wrap { max-width: 900px; padding-top: 46px; }
        body.template-app_download .shell {
            border-color: rgba(255,255,255,.14);
            border-radius: 38px;
            background: #0f172a;
            color: #fff;
        }
        body.template-app_download .topbar { background: rgba(255,255,255,.04); }
        body.template-app_download .hero { grid-template-columns: 1fr 310px; }
        body.template-app_download .main-copy { display: flex; flex-direction: column; justify-content: center; }
        body.template-app_download .side { border-left-color: rgba(255,255,255,.12); background: transparent; }
        body.template-app_download .media {
            min-height: 360px;
            border: 12px solid #020617;
            border-radius: 38px;
        }
        body.template-app_download .info,
        body.template-app_download .section-card,
        body.template-app_download .link-item { background: rgba(255,255,255,.07); color: #fff; }
        body.template-app_download .info .k { color: #b6c8dd; }
        body.template-app_download .info .v,
        body.template-app_download .product-title,
        body.template-app_download .link-item span:first-child { color: #fff; }

        body.template-payment_card {
            background: linear-gradient(145deg, #111827, #0f766e);
        }
        body.template-payment_card .wrap { max-width: 540px; padding-top: 54px; }
        body.template-payment_card .shell {
            border: 1px solid rgba(255,255,255,.18);
            border-radius: 28px;
            background: linear-gradient(145deg, #fff, #eff6ff);
            box-shadow: 0 40px 90px -46px #000;
        }
        body.template-payment_card .topbar { background: transparent; border-bottom: 0; }
        body.template-payment_card .hero { display: block; }
        body.template-payment_card .main-copy { padding: 34px 34px 26px; text-align: left; }
        body.template-payment_card h1 { font-size: clamp(34px, 8vw, 54px); }
        body.template-payment_card .side { padding: 0 24px 28px; border: 0; background: transparent; }
        body.template-payment_card .details { grid-template-columns: 1fr; }
        body.template-payment_card .info { border-radius: 18px; background: #fff; }

        body.template-link_hub {
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 46%, #020617 100%);
            color: #fff;
        }
        body.template-link_hub .wrap { max-width: 560px; padding-top: 34px; }
        body.template-link_hub .shell {
            border-color: rgba(255,255,255,.14);
            border-radius: 32px;
            background: rgba(15,23,42,.86);
            box-shadow: 0 34px 90px -48px #000;
        }
        body.template-link_hub .topbar {
            justify-content: center;
            border: 0;
            background: transparent;
            text-align: center;
        }
        body.template-link_hub .brand-mini { flex-direction: column; }
        body.template-link_hub .hero { display: block; }
        body.template-link_hub .main-copy { padding: 18px 34px 28px; text-align: center; }
        body.template-link_hub h1 { color: #fff; font-size: clamp(36px, 9vw, 56px); }
        body.template-link_hub .lead { color: #cbd5e1; }
        body.template-link_hub .side { display: none; }
        body.template-link_hub .sections { padding: 0 24px 28px; }
        body.template-link_hub .section-card {
            border: 0;
            padding: 0;
            background: transparent;
        }
        body.template-link_hub .section-head { display: none; }
        body.template-link_hub .link-list { grid-template-columns: 1fr; }
        body.template-link_hub .link-item {
            min-height: 62px;
            border-color: rgba(255,255,255,.14);
            border-radius: 20px;
            background: rgba(255,255,255,.08);
            color: #fff;
        }
        body.template-link_hub .info .k { color: #b6c8dd; }
        body.template-link_hub .info .v,
        body.template-link_hub .product-title,
        body.template-link_hub .link-item span:first-child { color: #fff; }

        body.template-sidebar_profile .details .info .v,
        body.template-menu_board .details .info .v,
        body.template-app_download .details .info .v,
        body.template-link_hub .details .info .v {
            color: #f8fafc !important;
        }
        body.template-sidebar_profile .details .info .k,
        body.template-menu_board .details .info .k,
        body.template-app_download .details .info .k,
        body.template-link_hub .details .info .k {
            color: #b6c8dd !important;
        }
        body.template-sidebar_profile .details .info,
        body.template-menu_board .details .info,
        body.template-app_download .details .info,
        body.template-link_hub .details .info {
            border-color: rgba(226,232,240,.28);
            background: rgba(255,255,255,.08);
        }

        @media (max-width: 820px) {
            .hero { grid-template-columns: 1fr; }
            .side { border-left: 0; border-top: 1px solid var(--line); }
            .products, .link-list, .form-grid { grid-template-columns: 1fr; }
            .template-catalog_grid .hero { grid-template-columns: 1fr; }
            .template-catalog_grid .products { grid-template-columns: 1fr; }
            .template-editorial .side { grid-template-columns: 1fr; }
            .template-compact_banner .side,
            .template-booking_card .details { grid-template-columns: 1fr; }
            .template-sidebar_profile .hero,
            .template-lead_capture .hero,
            .template-resume_sheet .hero { grid-template-columns: 1fr; }
            body.template-compact_banner .shell { border-radius: 28px; }
            body.template-compact_banner .hero,
            body.template-app_download .hero,
            body.template-catalog_grid .hero { grid-template-columns: 1fr; }
            body.template-sidebar_profile .side { border-right: 0; }
            body.template-menu_board .product,
            body.template-menu_board .link-item { grid-template-columns: 72px minmax(0,1fr); }
        }
        @media (max-width: 560px) {
            .wrap { padding: 12px; }
            .shell { border-radius: 24px; }
            .topbar { align-items: flex-start; }
            .main-copy { padding: 26px 20px; }
            .side { padding: 14px; }
            h1 { font-size: clamp(31px, 12vw, 44px); }
            .btn { width: 100%; }
            .product { grid-template-columns: 1fr; }
            .product img { width: 100%; height: 190px; }
            body.template-event_pass .shell::before,
            body.template-event_pass .shell::after { display: none; }
            body.template-editorial .side,
            body.template-booking_card .details { grid-template-columns: 1fr; }
        }
    </style>
</head>
@php
    $publicTemplate = preg_replace('/[^a-z0-9_-]/i', '', (string) data_get($qrCode->settings, 'public_template', 'split_showcase')) ?: 'split_showcase';
    $templatePalettes = [
        'split_showcase' => ['primary' => '#2563eb', 'accent' => '#14b8a6'],
        'mobile_stack' => ['primary' => '#0f172a', 'accent' => '#38bdf8'],
        'catalog_grid' => ['primary' => '#0f766e', 'accent' => '#2563eb'],
        'editorial' => ['primary' => '#334155', 'accent' => '#9a3412'],
        'compact_banner' => ['primary' => '#2563eb', 'accent' => '#0891b2'],
        'sidebar_profile' => ['primary' => '#047857', 'accent' => '#2563eb'],
        'menu_board' => ['primary' => '#eab308', 'accent' => '#22c55e'],
        'lead_capture' => ['primary' => '#be3c78', 'accent' => '#2563eb'],
        'event_pass' => ['primary' => '#3157a4', 'accent' => '#0891b2'],
        'file_card' => ['primary' => '#475569', 'accent' => '#2563eb'],
        'review_spotlight' => ['primary' => '#b7791f', 'accent' => '#2563eb'],
        'resume_sheet' => ['primary' => '#0f172a', 'accent' => '#64748b'],
        'booking_card' => ['primary' => '#0891b2', 'accent' => '#047857'],
        'app_download' => ['primary' => '#2563eb', 'accent' => '#2dd4bf'],
        'payment_card' => ['primary' => '#4f46e5', 'accent' => '#0f766e'],
        'link_hub' => ['primary' => '#2563eb', 'accent' => '#06b6d4'],
    ];
    $fallbackPalette = $templatePalettes[$publicTemplate] ?? $templatePalettes['split_showcase'];
    $initialBrandKit = $brandKit ?? null;
    $pagePrimary = trim((string) ($initialBrandKit?->primary_color ?? '')) ?: $fallbackPalette['primary'];
    $pageAccent = trim((string) ($initialBrandKit?->accent_color ?? '')) ?: $fallbackPalette['accent'];
@endphp
<body class="template-{{ $publicTemplate }}" style="--primary: {{ $pagePrimary }}; --accent: {{ $pageAccent }};">
@php
    $brandKit = $brandKit ?? null;
    $brandPrimary = $pagePrimary;
    $brandAccent = $pageAccent;
    $brandLogo = trim((string) ($brandKit?->logo_url ?? ''));
    $brandOps = app(\Modules\AppBrandKit\Support\BrandOperations::class);
    $utmPreset = $brandOps->utmPreset((int) data_get($qrCode->settings, 'utm_preset_id'), (int) $qrCode->owner_user_id);
    $title = trim((string) data_get($content, 'title')) ?: $qrCode->name;
    $description = trim((string) data_get($content, 'description', data_get($content, 'summary', data_get($content, 'message', ''))));
    $cover = trim((string) (data_get($content, 'background_image_url') ?: data_get($content, 'hero_image_url') ?: data_get($content, 'logo_url') ?: ''));
    $products = collect((array) data_get($content, 'products', []))->filter(fn ($item) => trim((string) data_get($item, 'name')) !== '');
    $links = collect((array) data_get($content, 'links', []))->filter(fn ($item) => trim((string) data_get($item, 'label')) !== '');
    $fields = collect((array) data_get($content, 'fields', []))->filter(fn ($item) => trim((string) data_get($item, 'label')) !== '');

    $typeKey = (string) $qrCode->type;
    $ctaUrl = trim((string) match ($typeKey) {
        'business_review', 'google_review' => data_get($content, 'review_url', ''),
        'app_download' => data_get($content, 'ios_url') ?: data_get($content, 'android_url') ?: data_get($content, 'fallback_url', ''),
        'file_upload' => data_get($content, 'file_url', ''),
        'event' => data_get($content, 'rsvp_url', ''),
        'booking' => data_get($content, 'booking_url', data_get($content, 'url', '')),
        'resume_qr_code' => data_get($content, 'portfolio_url') ?: data_get($content, 'resume_file_url', ''),
        default => data_get($content, 'cta_url') ?: data_get($content, 'website') ?: data_get($content, 'url') ?: data_get($content, 'file_url') ?: '',
    });
    $ctaLabel = trim((string) data_get($content, 'cta_label', data_get($content, 'button_label', '')));
    $ctaLabel = $ctaLabel !== '' ? $ctaLabel : match ($typeKey) {
        'business_review', 'google_review' => __('Leave a review'),
        'app_download' => __('Download app'),
        'file_upload' => __('Open file'),
        'event' => __('RSVP'),
        'booking' => __('Book now'),
        'resume_qr_code' => __('View profile'),
        default => __('Open link'),
    };

    $phone = trim((string) data_get($content, 'phone', data_get($content, 'whatsapp_number', '')));
    $email = trim((string) data_get($content, 'email'));
    $address = trim((string) data_get($content, 'address', data_get($content, 'location', '')));
    $hours = trim((string) data_get($content, 'opening_hours'));
    $category = trim((string) data_get($content, 'category', data_get($content, 'job_title', data_get($content, 'subtitle', ''))));
    $startsAt = trim((string) data_get($content, 'starts_at'));
    $endsAt = trim((string) data_get($content, 'ends_at'));
    $company = trim((string) data_get($content, 'company'));
    $website = trim((string) data_get($content, 'website'));
    $accessNote = trim((string) data_get($content, 'access_note'));
    $fallbackDestination = trim((string) $qrCode->destination_url);

    $facts = collect([
        ['key' => 'CAT', 'label' => __('Category'), 'value' => $category],
        ['key' => 'CO', 'label' => __('Company'), 'value' => $company],
        ['key' => 'DT', 'label' => __('When'), 'value' => trim($startsAt.($endsAt !== '' ? ' - '.$endsAt : ''))],
        ['key' => 'TEL', 'label' => __('Phone'), 'value' => $phone, 'href' => $phone !== '' ? 'tel:'.$phone : ''],
        ['key' => '@', 'label' => __('Email'), 'value' => $email, 'href' => $email !== '' ? 'mailto:'.$email : ''],
        ['key' => 'WEB', 'label' => __('Website'), 'value' => $website, 'href' => $website],
        ['key' => 'MAP', 'label' => __('Address'), 'value' => $address],
        ['key' => 'HRS', 'label' => __('Opening hours'), 'value' => $hours],
        ['key' => 'TXT', 'label' => __('Access note'), 'value' => $accessNote],
    ])->filter(fn ($item) => trim((string) $item['value']) !== '')->values();
@endphp
<main class="wrap">
    <section class="shell">
        <div class="topbar">
            <div class="brand-mini">
                <div class="mark">
                    @if ($brandLogo !== '')
                        <img src="{{ $brandLogo }}" alt="">
                    @else
                        {{ strtoupper(substr($title, 0, 1)) }}
                    @endif
                </div>
                <div class="min-w-0">
                    <span class="label">{{ $typeLabel }}</span>
                    <p class="topbar-title">{{ $title }}</p>
                </div>
            </div>
        </div>

        <div class="hero">
            <div class="main-copy">
                <p class="eyebrow">{{ $typeLabel }}</p>
                <h1>{{ $title }}</h1>
                @if ($description !== '')
                    <p class="lead">{{ $description }}</p>
                @elseif ($fallbackDestination !== '')
                    <p class="lead">{{ __('Scan-safe public page. The destination can be updated without changing the printed QR code.') }}</p>
                @endif

                <div class="actions">
                    @if ($ctaUrl !== '')
                        <a class="btn" href="{{ $brandOps->applyUtm($ctaUrl, $utmPreset) }}">{{ $ctaLabel }}</a>
                    @endif
                    @if ($phone !== '')
                        <a class="btn secondary" href="tel:{{ $phone }}">{{ __('Call') }}</a>
                    @endif
                    @if ($email !== '')
                        <a class="btn secondary" href="mailto:{{ $email }}">{{ __('Email') }}</a>
                    @endif
                </div>
            </div>

            <aside class="side">
                @if ($cover !== '')
                    <div class="media">
                        <img src="{{ $cover }}" alt="">
                    </div>
                @else
                    <div class="media placeholder">
                        <div class="preview-card">
                            <p class="label" style="color: rgba(255,255,255,.78);">{{ $typeLabel }}</p>
                            <p class="preview-title">{{ $title }}</p>
                            <div class="preview-meta">
                                @if ($category !== '')
                                    <span class="preview-chip">{{ $category }}</span>
                                @endif
                                @if ($ctaUrl !== '')
                                    <span class="preview-chip">{{ $ctaLabel }}</span>
                                @endif
                                @if ($facts->count() > 1)
                                    <span class="preview-chip">{{ __('Contact') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                @if ($facts->isNotEmpty())
                    <div class="details">
                        @foreach ($facts as $fact)
                            @php $href = trim((string) ($fact['href'] ?? '')); @endphp
                            @if ($href !== '')
                                <a class="info" href="{{ $href }}">
                                    <span class="info-key">{{ $fact['key'] }}</span>
                                    <span>
                                        <p class="k">{{ $fact['label'] }}</p>
                                        <p class="v">{{ $fact['value'] }}</p>
                                    </span>
                                </a>
                            @else
                                <div class="info">
                                    <span class="info-key">{{ $fact['key'] }}</span>
                                    <span>
                                        <p class="k">{{ $fact['label'] }}</p>
                                        <p class="v">{{ $fact['value'] }}</p>
                                    </span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif
            </aside>
        </div>

        @if ($products->isNotEmpty() || $links->isNotEmpty() || $fields->isNotEmpty())
            <div class="sections">
                @if ($products->isNotEmpty())
                    <section class="section-card">
                        <div class="section-head">
                            <div>
                                <p class="label">{{ __('Collection') }}</p>
                                <h2>{{ $typeKey === 'restaurant_menu' ? __('Menu highlights') : __('Featured items') }}</h2>
                            </div>
                        </div>
                        <div class="grid products">
                            @foreach ($products as $product)
                                <article class="product">
                                    @if (trim((string) data_get($product, 'image_url')) !== '')
                                        <img src="{{ data_get($product, 'image_url') }}" alt="">
                                    @else
                                        <div style="width:86px;height:86px;border-radius:15px;background:linear-gradient(135deg,var(--primary),var(--accent));"></div>
                                    @endif
                                    <div>
                                        <p class="product-title">{{ data_get($product, 'name') }}</p>
                                        @if (trim((string) data_get($product, 'description')) !== '')
                                            <p class="product-desc">{{ data_get($product, 'description') }}</p>
                                        @endif
                                        @if (trim((string) data_get($product, 'price')) !== '')
                                            <span class="price">{{ data_get($product, 'price') }}</span>
                                        @endif
                                        @if (trim((string) data_get($product, 'url')) !== '')
                                            <div class="actions" style="margin-top: 12px;">
                                                <a class="btn secondary" href="{{ $brandOps->applyUtm((string) data_get($product, 'url'), $utmPreset) }}">{{ __('View') }}</a>
                                            </div>
                                        @endif
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($links->isNotEmpty())
                    <section class="section-card">
                        <div class="section-head">
                            <div>
                                <p class="label">{{ __('Links') }}</p>
                                <h2>{{ __('Choose where to go next') }}</h2>
                            </div>
                        </div>
                        <div class="grid link-list">
                            @foreach ($links as $link)
                                <a class="link-item" href="{{ trim((string) data_get($link, 'url')) !== '' ? $brandOps->applyUtm((string) data_get($link, 'url'), $utmPreset) : '#' }}">
                                    <span>{{ data_get($link, 'label') }}</span>
                                    <span>{{ __('Open') }}</span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if ($fields->isNotEmpty())
                    <section class="section-card">
                        <div class="section-head">
                            <div>
                                <p class="label">{{ __('Form') }}</p>
                                <h2>{{ data_get($content, 'submit_label') ?: __('Send details') }}</h2>
                            </div>
                        </div>
                        <div class="grid form-grid">
                            @foreach ($fields as $field)
                                <div class="field-preview">{{ data_get($field, 'label') }}{{ data_get($field, 'required') ? ' *' : '' }}</div>
                            @endforeach
                        </div>
                    </section>
                @endif
            </div>
        @endif
    </section>
</main>
</body>
</html>
