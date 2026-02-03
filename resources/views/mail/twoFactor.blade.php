@php
  $primaryColor = ($settings->theme_colors ?? [])['primary'] ?? '#20246b';
  $secondaryColor = ($settings->theme_colors ?? [])['secondary'] ?? '#ebf5ff';
  $tertiaryColor = ($settings->theme_colors ?? [])['tertiary'] ?? '#ffcf01';
@endphp
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta charset="UTF-8">
  <title>Two-Factor Authentication Code - {{ config('app.name') }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <?php echo "<style>"; ?>
    /* Client resets */
    body,
    table,
    td,
    a {
      -webkit-text-size-adjust: 100%;
      -ms-text-size-adjust: 100%;
    }

    img {
      -ms-interpolation-mode: bicubic;
      border: 0;
      outline: none;
      text-decoration: none;
      display: block;
    }

    table {
      border-collapse: collapse !important;
    }

    body {
      margin: 0;
      padding: 0;
      width: 100% !important;
      height: 100% !important;
      background-color: #f4f6f8;
    }

    /* Typography */
    .font {
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      color: <?php echo $primaryColor; ?>;
    }

    .muted {
      color: #475569;
    }

    .small {
      font-size: 12px;
      color: #64748b;
    }

    /* Card */
    .card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      overflow: hidden;
    }

    .content {
      padding: 32px;
    }

    .divider {
      height: 1px;
      background: #e2e8f0;
      line-height: 1px;
    }

    /* OTP Box */
    .otp-box {
      background: <?php echo $primaryColor; ?>;
      border-radius: 12px;
      padding: 32px;
      text-align: center;
      margin: 24px 0;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .otp-code {
      font-size: 48px;
      font-weight: 700;
      color: #ffffff;
      letter-spacing: 12px;
      margin: 0;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .otp-label {
      color: #e0e7ff;
      font-size: 14px;
      font-weight: 600;
      margin: 12px 0 0;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .otp-expiry {
      color: #c7d2fe;
      font-size: 13px;
      margin: 8px 0 0;
    }

    /* Alert boxes */
    .alert-warning {
      background: <?php echo $tertiaryColor; ?>20;
      border-left: 4px solid <?php echo $tertiaryColor; ?>;
      padding: 16px;
      border-radius: 6px;
      margin: 16px 0;
    }

    .alert-info {
      background: <?php echo $secondaryColor; ?>;
      border-left: 4px solid <?php echo $primaryColor; ?>;
      padding: 16px;
      border-radius: 6px;
      margin: 16px 0;
    }

    /* Security Features List */
    .security-list {
      background: <?php echo $secondaryColor; ?>;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      padding: 20px;
      margin: 20px 0;
    }

    .security-item {
      display: flex;
      align-items: flex-start;
      margin-bottom: 12px;
    }

    .security-item:last-child {
      margin-bottom: 0;
    }

    .security-icon {
      color: #16a34a;
      margin-right: 12px;
      font-size: 18px;
      line-height: 1.5;
    }

    /* Button */
    .btn a {
      display: inline-block;
      padding: 14px 22px;
      background: <?php echo $primaryColor; ?>;
      color: #ffffff !important;
      text-decoration: none;
      font-weight: 600;
      font-size: 16px;
      border-radius: 8px;
      border: 1px solid <?php echo $primaryColor; ?>;
    }

    /* Responsive */
    @media only screen and (max-width: 600px) {
      .container {
        width: 100% !important;
      }

      .content {
        padding: 24px !important;
      }

      h1 {
        font-size: 22px !important;
      }

      h2 {
        font-size: 18px !important;
      }

      .otp-code {
        font-size: 36px !important;
        letter-spacing: 8px !important;
      }

      .otp-box {
        padding: 24px !important;
      }

      .btn a {
        width: 100% !important;
        text-align: center !important;
        padding: 14px 18px !important;
      }

      .logo {
        height: 56px !important;
        width: 56px !important;
      }
    }
  <?php echo "</style>"; ?>

  <!--[if mso]>
  <style type="text/css">
    table, td { mso-table-lspace:0pt !important; mso-table-rspace:0pt !important; }
    .font { font-family: Arial, sans-serif !important; }
  </style>
  <![endif]-->
</head>

<body>
  <!-- Preheader -->
  <div style="display:none; overflow:hidden; line-height:1px; opacity:0; max-height:0; max-width:0;">
    Your two-factor authentication code for {{ config('app.name') }}
  </div>

  <center role="article" aria-roledescription="email" lang="en" style="width:100%; background:#f4f6f8;">
    <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td align="center" style="padding:24px;">
          <table role="presentation" class="container card" width="640" cellpadding="0" cellspacing="0"
            style="width:640px; max-width:640px;">

            <!-- Brand header -->
            <tr>
              <td align="center" style="background:<?php echo $primaryColor; ?>; padding:18px 24px;">
                <table role="presentation" width="100%">
                  <tr>
                    <td align="left" class="font"
                      style="color:#ffffff; font-weight:600; font-size:16px;">
                      {{ $msg['brand_name'] ?? config('app.name', 'Library Management System') }}
                    </td>
                    <td align="right">
                      @if(isset($logoData) && $logoData)
                          <img src="{{ $message->embedData($logoData, 'logo.png', 'image/png') }}" alt="{{ $msg['brand_logo_alt'] ?? 'Logo' }}" class="logo" style="height:48px; width:48px;">
                      @else
                          <img src="{{ $message->embed($defaultLogoPath ?? public_path('img/OwlQuery.png')) }}" alt="{{ $msg['brand_logo_alt'] ?? 'Logo' }}" class="logo" style="height:48px; width:48px;">
                      @endif
                    </td>
                  </tr>
                </table>
              </td>
            </tr>

            <!-- Main content -->
            <tr>
              <td class="content font">
                <!-- Header Icon -->
                <div style="text-align:center; margin-bottom:20px;">
                  <div style="display:inline-block; background:<?php echo $primaryColor; ?>; border-radius:50%; padding:20px; width:60px; height:60px;">
                    <span style="font-size:36px;">🔐</span>
                  </div>
                </div>

                <h1 style="margin:0 0 8px; font-size:28px; font-weight:700; color:<?php echo $primaryColor; ?>; text-align:center;">
                  {{ $msg['title'] ?? 'Two-Factor Authentication' }}
                </h1>

                <p class="muted" style="margin:0 0 16px; text-align:center; font-size:16px;">
                  {{ $msg['greeting'] ?? 'Dear ' . $user->first_name . ' ' . $user->last_name . ',' }}
                </p>

                <!-- Info Alert -->
                <div class="alert-info">
                  <p style="margin:0; color:<?php echo $primaryColor; ?>; font-weight:600;">
                    🔔 Login Verification Required
                  </p>
                  <p style="margin:8px 0 0; color:<?php echo $primaryColor; ?>; font-size:14px;">
                    {{ $msg['intro'] ?? 'Someone is attempting to access your account. If this was you, use the code below to complete your login.' }}
                  </p>
                </div>

                <!-- OTP Box -->
                <div class="otp-box">
                  <p class="otp-label">{{ $msg['otp_label'] ?? 'Your Verification Code' }}</p>
                  <p class="otp-code">{{ $otp }}</p>
                  <p class="otp-expiry">{{ $msg['otp_expiry'] ?? '⏱️ This code expires in 10 minutes' }}</p>
                </div>

                <!-- Security Warning -->
                <div class="alert-warning">
                  <p style="margin:0; color:#92400e; font-weight:600; font-size:14px;">
                    ⚠️ Security Notice
                  </p>
                  <p style="margin:8px 0 0; color:#92400e; font-size:14px;">
                    {{ $msg['security_notice'] ?? 'If you did not attempt to log in, please ignore this email and ensure your password is secure. Never share this code with anyone, including library staff.' }}
                  </p>
                </div>

                <!-- Security Features -->
                <h2 style="margin:24px 0 12px; font-size:18px; color:<?php echo $primaryColor; ?>; font-weight:700;">
                  For Your Security
                </h2>
                <div class="security-list">
                  @foreach($msg['security_tips'] ?? ['This code can only be used once', 'Do not share this code with anyone', 'Our team will never ask for this code', 'Code expires automatically after 10 minutes'] as $tip)
                  <div class="security-item">
                    <span class="security-icon">✓</span>
                    <span class="muted" style="font-size:14px;">{{ $tip }}</span>
                  </div>
                  @endforeach
                </div>

                <!-- Additional Info -->
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:16px; margin:20px 0;">
                  <p style="margin:0; color:#475569; font-size:14px;">
                    <strong>Having trouble?</strong><br>
                    {{ $msg['help_text'] ?? 'If you didn\'t receive the code or it has expired, you can request a new one by returning to the login page and clicking "Resend Code."' }}
                  </p>
                </div>

                <!-- CTA Button -->
                <table role="presentation" cellpadding="0" cellspacing="0" class="btn"
                  style="margin:24px 0 18px; width:100%;">
                  <tr>
                    <td align="center">
                      <!--[if mso]>
                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" href="{{ $msg['cta_url'] ?? env('E_LIBRARY_URL') }}" style="height:44px; v-text-anchor:middle; width:220px;" arcsize="10%" strokecolor="<?php echo $primaryColor; ?>" fillcolor="<?php echo $primaryColor; ?>">
                          <w:anchorlock/>
                          <center style="color:#ffffff; font-family:Segoe UI, Arial, sans-serif; font-size:16px; font-weight:600;">
                            {{ $msg['cta_label'] ?? 'Go to Login Page' }}
                          </center>
                        </v:roundrect>
                      <![endif]-->
                      <!--[if !mso]><!-- -->
                      <a href="{{ $msg['cta_url'] ?? env('E_LIBRARY_URL') }}">{{ $msg['cta_label'] ?? 'Go to Login Page' }}</a>
                      <!--<![endif]-->
                    </td>
                  </tr>
                </table>

                <div class="divider" style="margin:24px 0;"></div>

                <!-- Footer -->
                <p class="muted" style="margin:0 0 12px;">
                  If you have any security concerns, please contact us immediately:
                </p>
                <p class="small" style="margin:0 0 12px;">
                  📧 Email: {{ $msg['contact_email'] ?? 'owlquery.tech@gmail.com' }}<br>
                  📞 Phone: {{ $msg['contact_phone'] ?? '(02) 8252-9613' }}<br>
                  🕐 Hours: {{ $msg['contact_hours'] ?? 'Monday-Friday, 8:00 AM - 5:00 PM' }}
                </p>

                <p class="muted" style="margin:12px 0 0;">{{ $msg['thanks'] ?? 'Thank you for keeping your account secure.' }}</p>
                <p class="small" style="margin:8px 0 0;">{{ $msg['footer'] ?? 'This is an automated security message. Please do not reply to this email.' }}</p>

                <p class="small" style="margin:16px 0 0; text-align:center; color:#94a3b8;">
                  © {{ date('Y') }} {{ $msg['brand_name'] ?? config('app.name') }}. All rights reserved.
                </p>
              </td>
            </tr>
          </table>
          <!-- /card -->
        </td>
      </tr>
    </table>
  </center>
</body>

</html>