<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('New contact enquiry') }}</title>
</head>
<body style="margin:0; padding:0; background-color:#F1F5F9; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif; color:#0F172A;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#F1F5F9; padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background-color:#FFFFFF; border-radius:16px; overflow:hidden; box-shadow:0 1px 3px rgba(15,23,42,0.08);">
                    <tr>
                        <td style="background:linear-gradient(120deg, #4F46E5, #0F172A); padding:28px 32px;">
                            <h1 style="margin:0; font-size:18px; font-weight:700; color:#FFFFFF;">
                                {{ __('New contact enquiry') }}
                            </h1>
                            <p style="margin:6px 0 0; font-size:13px; color:rgba(255,255,255,0.75);">
                                {{ $subjectLabel }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding:0 0 14px; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.04em; color:#64748B;">{{ __('From') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:0 0 20px; font-size:15px; color:#0F172A;">
                                        <strong>{{ $fullName }}</strong><br>
                                        <a href="mailto:{{ $email }}" style="color:#4F46E5; text-decoration:none;">{{ $email }}</a>
                                        @if (!empty($company))
                                            <br><span style="color:#64748B;">{{ $company }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 0 14px; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.04em; color:#64748B; border-top:1px solid #E2E8F0; padding-top:20px;">{{ __('Message') }}</td>
                                </tr>
                                <tr>
                                    <td style="font-size:15px; line-height:1.6; color:#0F172A; white-space:pre-wrap;">{{ $messageBody }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:18px 32px; background-color:#F8FAFC; border-top:1px solid #E2E8F0; font-size:12px; color:#94A3B8;">
                            {{ __('Reply directly to this email to respond to the sender.') }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
