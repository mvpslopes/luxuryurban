<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="only light">
    <meta name="theme-color" content="#F3F4F6">
    <title>Login — Luxury Urban</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">
    <style>
        /* Estilos críticos inline — garantem login correto mesmo com cache ou dark mode do SO */
        html {
            color-scheme: only light !important;
            background-color: #F3F4F6 !important;
        }
        body.auth-page {
            margin: 0;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            background-color: #F3F4F6 !important;
            color: #111827 !important;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            font-size: 14px;
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
        }
        .auth-card {
            width: 100%;
            max-width: 400px;
            background-color: #FFFFFF !important;
            border: 1px solid #E5E7EB;
            border-radius: 16px;
            padding: 40px 36px;
            box-shadow: 0 8px 32px rgba(17, 24, 39, 0.08);
        }
        .auth-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 8px;
        }
        .auth-logo .brand-icon {
            width: 44px;
            height: 44px;
            background-color: #5B5DF6 !important;
            color: #FFFFFF !important;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 15px;
            flex-shrink: 0;
        }
        .auth-logo .brand-text {
            font-size: 20px;
            font-weight: 700;
            color: #111827 !important;
        }
        .auth-subtitle {
            text-align: center;
            color: #6B7280 !important;
            font-size: 14px;
            margin: 0 0 28px;
        }
        .auth-form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .auth-form .field label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151 !important;
            margin-bottom: 8px;
        }
        .auth-form .field input {
            display: block;
            width: 100%;
            height: 44px;
            padding: 0 14px;
            font-size: 15px;
            font-family: inherit;
            color: #111827 !important;
            background-color: #FFFFFF !important;
            border: 1px solid #D1D5DB !important;
            border-radius: 10px;
            outline: none;
            box-sizing: border-box;
            -webkit-appearance: none;
            appearance: none;
        }
        .auth-form .field input:focus {
            border-color: #5B5DF6 !important;
            box-shadow: 0 0 0 3px rgba(91, 93, 246, 0.15) !important;
        }
        .auth-form .field input:-webkit-autofill,
        .auth-form .field input:-webkit-autofill:hover,
        .auth-form .field input:-webkit-autofill:focus {
            -webkit-text-fill-color: #111827 !important;
            -webkit-box-shadow: 0 0 0 1000px #FFFFFF inset !important;
            box-shadow: 0 0 0 1000px #FFFFFF inset !important;
            border: 1px solid #D1D5DB !important;
        }
        .auth-form .btn-submit {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 46px;
            margin-top: 8px;
            padding: 0 16px;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            color: #FFFFFF !important;
            background-color: #5B5DF6 !important;
            border: none;
            border-radius: 10px;
            cursor: pointer;
        }
        .auth-form .btn-submit:hover {
            background-color: #4F4FE0 !important;
        }
        .auth-alert {
            padding: 12px 14px;
            margin-bottom: 20px;
            border-radius: 10px;
            font-size: 14px;
            color: #B91C1C !important;
            background-color: #FEF2F2 !important;
            border: 1px solid #FECACA;
        }
        @media (max-width: 480px) {
            body.auth-page { padding: 16px; }
            .auth-card { padding: 28px 20px; }
        }
        @media (prefers-color-scheme: dark) {
            html, body.auth-page {
                background-color: #F3F4F6 !important;
                color: #111827 !important;
            }
            .auth-card {
                background-color: #FFFFFF !important;
            }
            .auth-logo .brand-text,
            .auth-form .field label,
            .auth-form .field input {
                color: #111827 !important;
            }
            .auth-form .field input {
                background-color: #FFFFFF !important;
            }
        }
    </style>
</head>
<body class="auth-page">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="brand-icon">LU</div>
            <span class="brand-text">Luxury Urban</span>
        </div>
        <p class="auth-subtitle">Sistema de gestão</p>

        <?php if ($msg = flash('error')): ?>
            <div class="auth-alert"><?= e($msg) ?></div>
        <?php endif; ?>

        <?= $content ?>
    </div>
</body>
</html>
