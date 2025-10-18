<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperação de Senha - AgroSistemas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
        }

        .email-container {
            max-width: 600px;
            margin: 40px auto;
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(16, 185, 129, 0.15);
        }

        .header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            padding: 40px 30px;
            text-align: center;
            color: white;
            position: relative;
        }

        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="30" r="1.5" fill="rgba(255,255,255,0.1)"/><circle cx="60" cy="70" r="2.5" fill="rgba(255,255,255,0.1)"/><circle cx="30" cy="80" r="1" fill="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.3;
        }

        .logo-container {
            position: relative;
            z-index: 1;
            margin-bottom: 20px;
        }

        .logo-circle {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            position: relative;
            backdrop-filter: blur(15px);
            border: 3px solid rgba(255, 255, 255, 0.25);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .logo-icon {
            font-size: 24px;
            color: white;
        }

        .logo-badge {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: rgba(0, 0, 0, 0.25);
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
        }

        .app-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .app-subtitle {
            font-size: 16px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .content {
            padding: 40px 30px;
        }

        .greeting {
            font-size: 24px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 20px;
        }

        .message {
            font-size: 16px;
            color: #4b5563;
            margin-bottom: 30px;
            line-height: 1.7;
        }

        .reset-button {
            display: inline-block;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            text-decoration: none;
            padding: 16px 32px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.35);
            margin: 20px 0;
        }

        .reset-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(16, 185, 129, 0.45);
        }

        .security-info {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 1px solid #f59e0b;
            border-radius: 12px;
            padding: 20px;
            margin: 30px 0;
        }

        .security-icon {
            color: #d97706;
            font-size: 20px;
            margin-right: 10px;
        }

        .security-title {
            color: #92400e;
            font-weight: 600;
            font-size: 16px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }

        .security-text {
            color: #a16207;
            font-size: 14px;
            line-height: 1.5;
        }

        .footer {
            background: #f9fafb;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e5e7eb;
        }

        .footer-text {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 15px;
        }

        .company-info {
            font-size: 12px;
            color: #9ca3af;
        }

        .divider {
            width: 60px;
            height: 3px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border-radius: 2px;
            margin: 20px auto;
        }

        .expiry-info {
            background: #f3f4f6;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }

        .expiry-text {
            color: #6b7280;
            font-size: 14px;
        }

        .expiry-time {
            color: #ef4444;
            font-weight: 600;
        }

        /* Responsivo */
        @media (max-width: 600px) {
            .email-container {
                margin: 20px;
                border-radius: 15px;
            }

            .header {
                padding: 30px 20px;
            }

            .content {
                padding: 30px 20px;
            }

            .footer {
                padding: 20px;
            }

            .app-title {
                font-size: 24px;
            }

            .greeting {
                font-size: 20px;
            }

            .reset-button {
                padding: 14px 24px;
                font-size: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="logo-container">
                <div class="logo-circle">
                    <span class="logo-icon">🌱</span>
                    <span class="logo-badge">🐄</span>
                </div>
            </div>
            <h1 class="app-title">AgroSistemas</h1>
            <p class="app-subtitle">Sistema de Gestão Agropecuária</p>
        </div>

        <!-- Content -->
        <div class="content">
            <h2 class="greeting">Olá!</h2>

            <p class="message">
                Você solicitou a redefinição de sua senha no <strong>AgroSistemas</strong>.
                Para criar uma nova senha, clique no botão abaixo:
            </p>

            <div style="text-align: center;">
                <a href="" class="reset-button"></a>
                {{-- <a href="{{ $resetUrl }}" class="reset-button"> --}}
                    🔐 Redefinir Minha Senha
                </a>
            </div>

            <div class="expiry-info">
                <p class="expiry-text">
                    Este link expira em <span class="expiry-time">60 minutos</span>
                </p>
            </div>

            <div class="security-info">
                <div class="security-title">
                    <span class="security-icon">🔒</span>
                    Informações de Segurança
                </div>
                <div class="security-text">
                    • Se você não solicitou esta redefinição, ignore este email<br>
                    • Nunca compartilhe este link com outras pessoas<br>
                    • Use uma senha forte com pelo menos 8 caracteres
                </div>
            </div>

            <div class="divider"></div>

            <p class="message" style="font-size: 14px; color: #6b7280;">
                Se o botão não funcionar, copie e cole este link no seu navegador:<br>
                {{-- <a href="{{ $resetUrl }}" style="color: #10b981; word-break: break-all;">{{ $resetUrl }}</a> --}}
                <a href="" style="color: #10b981; word-break: break-all;">Teste</a>
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p class="footer-text">
                Este email foi enviado automaticamente pelo sistema AgroSistemas.
            </p>
            <div class="company-info">
                <strong>AgroSistemas</strong> - Sistema de Gestão Agropecuária<br>
                © {{ date('Y') }} Todos os direitos reservados
            </div>
        </div>
    </div>
</body>
</html>
