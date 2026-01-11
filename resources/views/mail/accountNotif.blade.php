@php
  $primaryColor = ($settings->theme_colors ?? [])['primary'] ?? '#20246b';
  $secondaryColor = ($settings->theme_colors ?? [])['secondary'] ?? '#ebf5ff';
  $tertiaryColor = ($settings->theme_colors ?? [])['tertiary'] ?? '#ffcf01';
@endphp
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>{{ $msg['subject'] ?? 'Library Account' }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <style>
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

    /* Credential panel */
    .panel {
      background: <?php echo $secondaryColor; ?>;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
    }

    .panel-td {
      padding: 10px 12px;
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
  </style>

  <!--[if mso]>
  <style type="text/css">
    table, td { mso-table-lspace:0pt !important; mso-table-rspace:0pt !important; }
    .font { font-family: Arial, sans-serif !important; }
  </style>
  <![endif]-->
</head>

<body>
  <!-- Preheader (hidden in most clients) -->
  <div style="display:none; overflow:hidden; line-height:1px; opacity:0; max-height:0; max-width:0;">
    {{ $msg['intro'] ?? '' }}
  </div>

  <center role="article" aria-roledescription="email" lang="en" style="width:100%; background:#f4f6f8;">
    <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td align="center" style="padding:24px;">
          <!-- Outer card -->
          <table role="presentation" class="container card" width="640" cellpadding="0" cellspacing="0"
            style="width:640px; max-width:640px;">
            <!-- Top brand bar -->
            <tr>
              <td align="center" style="background:<?php echo $primaryColor; ?>; padding:18px 24px;">
                <table role="presentation" width="100%">
                  <tr>
                    <td align="left" class="font"
                      style="color:#ffffff; font-weight:600; font-size:16px;">
                      {{ $msg['brand_name'] ?? 'Library Management System' }}
                    </td>
                    <td align="right">
                      <img src="{{ $msg['brand_logo'] ?? asset('img/OwlQuery.png') }}" alt="{{ $msg['brand_logo_alt'] ?? 'Logo' }}" class="logo"
                        style="height:48px; width:48px;">
                    </td>
                  </tr>
                </table>
              </td>
            </tr>

            <!-- Main content -->
            <tr>
              <td class="content font">
                <h1 style="margin:0 0 8px; font-size:24px; font-weight:700; color:<?php echo $primaryColor; ?>;">
                  {{ $msg['title'] }}
                </h1>

                <p class="muted" style="margin:0 0 16px;">
                  {{ $msg['greeting'] }}
                </p>

                <p class="muted" style="margin:0 0 12px;">
                  {{ $msg['intro'] }}
                </p>
                <p class="muted" style="margin:0 0 20px;">
                  {{ $msg['instruction'] }}
                </p>

                <!-- Credentials panel -->
                <h2 style="margin:0 0 10px; font-size:18px; color:<?php echo $primaryColor; ?>; font-weight:700;">
                  {{ $msg['details_title'] }}
                </h2>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="panel"
                  style="margin:0 0 16px;">
                  <tr>
                    <td class="panel-td muted" width="180" style="font-weight:600;">{{ $msg['email_label'] }}:</td>
                    <td class="panel-td" style="font-weight:600; color:<?php echo $primaryColor; ?>;">{{ $user->email }}</td>
                  </tr>
                  <tr>
                    <td class="panel-td muted" width="180" style="font-weight:600;">{{ $msg['password_label'] }}:</td>
                    <td class="panel-td" style="font-weight:600; color:<?php echo $primaryColor; ?>;">{{ $password }}</td>
                  </tr>
                </table>

                <p class="muted" style="margin:0 0 20px;">
                  {{ $msg['reminder'] }}
                </p>

                <!-- Primary CTA (bulletproof) -->
                <table role="presentation" cellpadding="0" cellspacing="0" class="btn"
                  style="margin:8px 0 18px;">
                  <tr>
                    <td align="left">
                      <!--[if mso]>
                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" href="{{ $msg['cta_url'] }}" style="height:44px; v-text-anchor:middle; width:220px;" arcsize="10%" strokecolor="<?php echo $primaryColor; ?>" fillcolor="<?php echo $primaryColor; ?>">
                          <w:anchorlock/>
                          <center style="color:#ffffff; font-family:Segoe UI, Arial, sans-serif; font-size:16px; font-weight:600;">
                            {{ $msg['cta_label'] }}
                          </center>
                        </v:roundrect>
                      <![endif]-->
                      <!--[if !mso]><!-- -->
                      <a href="{{ $msg['cta_url'] }}">{{ $msg['cta_label'] }}</a>
                      <!--<![endif]-->
                    </td>
                  </tr>
                </table>

                <div class="divider" style="margin:24px 0;"></div>

                <p class="muted" style="margin:0 0 12px;">
                  {{ $msg['thanks'] }}
                </p>
                <p class="small" style="margin:0;">
                  {{ $msg['footer'] }}
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