<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">

<head>
  <meta charset="UTF-8">
  <title>Book Extension {{ ucfirst($transactionType) }} - {{ config('app.name') }}</title>
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
      color: #0f172a;
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

    /* Panel */
    .panel {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 8px;
    }

    .panel-td {
      padding: 10px 12px;
    }

    /* Status badges */
    .badge-success {
      background: #dcfce7;
      color: #166534;
      padding: 6px 12px;
      border-radius: 6px;
      font-weight: 600;
      font-size: 14px;
      display: inline-block;
    }

    .badge-danger {
      background: #fee2e2;
      color: #991b1b;
      padding: 6px 12px;
      border-radius: 6px;
      font-weight: 600;
      font-size: 14px;
      display: inline-block;
    }

    .badge-warning {
      background: #fef3c7;
      color: #92400e;
      padding: 6px 12px;
      border-radius: 6px;
      font-weight: 600;
      font-size: 14px;
      display: inline-block;
    }

    /* Alert boxes */
    .alert-success {
      background: #dcfce7;
      border-left: 4px solid #16a34a;
      padding: 16px;
      border-radius: 6px;
      margin: 16px 0;
    }

    .alert-danger {
      background: #fee2e2;
      border-left: 4px solid #dc2626;
      padding: 16px;
      border-radius: 6px;
      margin: 16px 0;
    }

    /* Button */
    .btn a {
      display: inline-block;
      padding: 14px 22px;
      background: #1e293b;
      color: #ffffff !important;
      text-decoration: none;
      font-weight: 600;
      font-size: 16px;
      border-radius: 8px;
      border: 1px solid #1e293b;
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
  <!-- Preheader -->
  <div style="display:none; overflow:hidden; line-height:1px; opacity:0; max-height:0; max-width:0;">
    @if($transactionType === 'extended')
    Your book extension request has been approved
    @else
    Your book extension request has been rejected
    @endif
  </div>

  <center role="article" aria-roledescription="email" lang="en" style="width:100%; background:#f4f6f8;">
    <table role="presentation" width="100%" border="0" cellpadding="0" cellspacing="0">
      <tr>
        <td align="center" style="padding:24px;">
          <table role="presentation" class="container card" width="640" cellpadding="0" cellspacing="0"
            style="width:640px; max-width:640px;">

            <!-- Brand header -->
            <tr>
              <td align="center" style="background:#0f172a; padding:18px 24px;">
                <table role="presentation" width="100%">
                  <tr>
                    <td align="left" class="font"
                      style="color:#ffffff; font-weight:600; font-size:16px;">
                      {{ config('app.name', 'BPS Library Management System') }}
                    </td>
                    <td align="right">
                      <img src="{{ asset('img/BPSLogo.png') }}" alt="BPS Logo" class="logo"
                        style="height:48px; width:48px;">
                    </td>
                  </tr>
                </table>
              </td>
            </tr>

            <!-- Main content -->
            <tr>
              <td class="content font">
                <!-- Status Badge -->
                <div style="margin-bottom: 20px;">
                  @if($transactionType === 'extended')
                  <span class="badge-success">✓ APPROVED</span>
                  @else
                  <span class="badge-danger">✗ REJECTED</span>
                  @endif
                </div>

                <h1 style="margin:0 0 8px; font-size:24px; font-weight:700; color:#0f172a;">
                  Extension Request {{ $transactionType === 'extended' ? 'Approved' : 'Rejected' }}
                </h1>

                <p class="muted" style="margin:0 0 16px;">
                  Dear {{ $user->first_name }} {{ $user->last_name }},
                </p>

                <!-- Alert Box -->
                @if($transactionType === 'extended')
                <div class="alert-success">
                  <p style="margin:0; color:#166534; font-weight:600;">
                    Good news! Your extension request has been approved.
                  </p>
                </div>
                @else
                <div class="alert-danger">
                  <p style="margin:0; color:#991b1b; font-weight:600;">
                    Your extension request has been rejected.
                  </p>
                </div>
                @endif

                <p class="muted" style="margin:20px 0;">
                  {{ $emailMessage }}
                </p>

                <!-- Book Details Panel -->
                <h2 style="margin:0 0 10px; font-size:18px; color:#0f172a; font-weight:700;">
                  Book Details
                </h2>
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" class="panel"
                  style="margin:0 0 16px;">
                  <tr>
                    <td class="panel-td muted" width="180" style="font-weight:600;">Book Title:</td>
                    <td class="panel-td" style="font-weight:600; color:#0f172a;">{{ $book->title }}</td>
                  </tr>
                  @if(isset($book->author))
                  <tr>
                    <td class="panel-td muted" width="180" style="font-weight:600;">Author:</td>
                    <td class="panel-td" style="font-weight:600; color:#0f172a;">{{ $book->author }}</td>
                  </tr>
                  @endif
                  @if(isset($book->accession))
                  <tr>
                    <td class="panel-td muted" width="180" style="font-weight:600;">Accession Number:</td>
                    <td class="panel-td" style="font-weight:600; color:#0f172a;">{{ $book->accession }}</td>
                  </tr>
                  @endif
                  @if($transactionType === 'extended')
                  <tr>
                    <td class="panel-td muted" width="180" style="font-weight:600;">New Due Date:</td>
                    <td class="panel-td" style="font-weight:600; color:#16a34a;">
                      {{ \Carbon\Carbon::parse($dueDate)->format('F j, Y') }}
                    </td>
                  </tr>
                  @else
                  <tr>
                    <td class="panel-td muted" width="180" style="font-weight:600;">Original Due Date:</td>
                    <td class="panel-td" style="font-weight:600; color:#dc2626;">
                      {{ \Carbon\Carbon::parse($dueDate)->format('F j, Y') }}
                    </td>
                  </tr>
                  @endif
                  <tr>
                    <td class="panel-td muted" width="180" style="font-weight:600;">Status:</td>
                    <td class="panel-td">
                      @if($transactionType === 'extended')
                      <span class="badge-success" style="font-size:12px; padding:4px 8px;">Extended</span>
                      @else
                      <span class="badge-danger" style="font-size:12px; padding:4px 8px;">Rejected</span>
                      @endif
                    </td>
                  </tr>
                </table>

                <!-- Important Notice -->
                @if($transactionType === 'extended')
                <div style="background:#fef3c7; border-left:4px solid #f59e0b; padding:16px; border-radius:6px; margin:16px 0;">
                  <p style="margin:0; color:#92400e; font-weight:600; font-size:14px;">
                    ⚠️ Important Reminder
                  </p>
                  <p style="margin:8px 0 0; color:#92400e; font-size:14px;">
                    Please return the book on or before the new due date to avoid penalties. Late returns may result in suspension of borrowing privileges.
                  </p>
                </div>
                @else
                <div style="background:#fef3c7; border-left:4px solid #f59e0b; padding:16px; border-radius:6px; margin:16px 0;">
                  <p style="margin:0; color:#92400e; font-weight:600; font-size:14px;">
                    ⚠️ Next Steps
                  </p>
                  <p style="margin:8px 0 0; color:#92400e; font-size:14px;">
                    Please return the book on or before the original due date. If you have questions about the rejection, please visit the library or contact us directly.
                  </p>
                </div>
                @endif

                <!-- CTA Button -->
                <table role="presentation" cellpadding="0" cellspacing="0" class="btn"
                  style="margin:24px 0 18px;">
                  <tr>
                    <td align="left">
                      <!--[if mso]>
                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" href="{{ config('app.url') }}" style="height:44px; v-text-anchor:middle; width:220px;" arcsize="10%" strokecolor="#1e293b" fillcolor="#1e293b">
                          <w:anchorlock/>
                          <center style="color:#ffffff; font-family:Segoe UI, Arial, sans-serif; font-size:16px; font-weight:600;">
                            View My Borrowings
                          </center>
                        </v:roundrect>
                      <![endif]-->
                      <!--[if !mso]><!-- -->
                      <a href="{{ config('app.url') }}">View My Borrowings</a>
                      <!--<![endif]-->
                    </td>
                  </tr>
                </table>

                <div class="divider" style="margin:24px 0;"></div>

                <!-- Footer -->
                <p class="muted" style="margin:0 0 12px;">
                  If you have any questions, please contact the library at:
                </p>
                <p class="small" style="margin:0 0 12px;">
                  📧 Email: owlquery.tech@gmail.com<br>
                  📞 Phone: (02) 8252-9613<br>
                  🕐 Hours: Monday-Friday, 8:00 AM - 5:00 PM
                </p>

                <p class="muted" style="margin:12px 0 0;">Thank you for using the BPS Library Management System.</p>
                <p class="small" style="margin:8px 0 0;">This is an automated message. Please do not reply to this email.</p>
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