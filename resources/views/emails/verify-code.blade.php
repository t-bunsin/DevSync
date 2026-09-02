{{--
    Registration code email.

    Hand-built rather than Laravel's markdown mailer: the code itself is the
    whole payload, so it gets the layout instead of being one more paragraph.
    Table layout and inline styles throughout — Outlook and Gmail strip <style>
    blocks and ignore flex/grid — and no remote images, so the design survives
    a client that blocks them.
--}}
@php
    $brandNavy = '#0b2638';
    $brandOcean = '#0f5f73';
    $brandTeal = '#1ca6a0';
    $ink = '#102a43';
    $inkSoft = '#486581';
    $line = '#dce5e7';
@endphp
<!DOCTYPE html>
<html lang="en" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="x-apple-disable-message-reformatting">
    {{-- Opt out of client-side dark-mode inversion: it mangles the code block. --}}
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>Your ZIN-WORKS verification code</title>
    <!--[if mso]>
    <style>body,table,td,p,a{font-family:"Segoe UI",Arial,sans-serif !important;}</style>
    <![endif]-->
</head>
<body style="margin:0;padding:0;width:100%;background-color:#eef4f4;color:{{ $ink }};-webkit-font-smoothing:antialiased;">

    {{-- Preheader: what the inbox preview line shows, hidden in the body. --}}
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
        {{ $code }} is your ZIN-WORKS code. It expires in {{ $ttlMinutes }} minutes.
        &#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;&#8203;
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
           style="background-color:#eef4f4;">
        <tr>
            <td align="center" style="padding:32px 16px;">

                <table role="presentation" width="560" cellpadding="0" cellspacing="0" border="0"
                       style="width:560px;max-width:100%;">

                    {{-- Brand lockup, sitting outside the card. --}}
                    <tr>
                        <td align="center" style="padding:0 0 22px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding-right:10px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td align="center" width="34" height="34"
                                                    style="width:34px;height:34px;background-color:{{ $brandTeal }};border-radius:9px;
                                                           font-family:'Segoe UI',Arial,sans-serif;font-size:14px;font-weight:700;
                                                           color:#ffffff;letter-spacing:0.5px;">
                                                    ZW
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                    <td style="font-family:'Segoe UI',Arial,sans-serif;font-size:17px;font-weight:700;
                                               letter-spacing:1.4px;color:{{ $brandNavy }};">
                                        ZIN<span style="color:{{ $brandTeal }};">-WORKS</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Card --}}
                    <tr>
                        <td style="background-color:#ffffff;border:1px solid {{ $line }};border-radius:18px;overflow:hidden;">

                            {{-- Header band. Solid fill under the gradient so Outlook still gets navy. --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                   style="background-color:{{ $brandNavy }};
                                          background-image:linear-gradient(135deg,{{ $brandNavy }} 0%,{{ $brandOcean }} 100%);">
                                <tr>
                                    <td style="padding:30px 36px 26px;font-family:'Segoe UI',Arial,sans-serif;">
                                        <p style="margin:0 0 10px;font-size:11px;font-weight:600;letter-spacing:1.6px;
                                                  text-transform:uppercase;color:#8fd6d2;">
                                            One last step
                                        </p>
                                        <h1 style="margin:0;font-size:25px;line-height:1.25;font-weight:700;color:#ffffff;">
                                            Confirm your email
                                        </h1>
                                    </td>
                                </tr>
                            </table>

                            {{-- Body --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding:32px 36px 8px;font-family:'Segoe UI',Arial,sans-serif;
                                               font-size:15px;line-height:1.6;color:{{ $inkSoft }};">
                                        <p style="margin:0;">
                                            Welcome to ZIN-WORKS. Enter this code in the tab you signed up from
                                            to finish creating your account.
                                        </p>
                                    </td>
                                </tr>

                                {{-- The code. Given the most weight on the page. --}}
                                <tr>
                                    <td style="padding:24px 36px 0;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                               style="background-color:#f2f9f8;border:1px solid #cfe7e5;border-radius:14px;">
                                            <tr>
                                                <td align="center" style="padding:26px 16px 10px;">
                                                    <span style="font-family:'SFMono-Regular',Consolas,'Courier New',monospace;
                                                                 font-size:38px;line-height:1;font-weight:700;
                                                                 letter-spacing:10px;text-indent:10px;color:{{ $brandNavy }};
                                                                 display:inline-block;">{{ $code }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td align="center" style="padding:0 16px 22px;font-family:'Segoe UI',Arial,sans-serif;
                                                                          font-size:12px;font-weight:600;letter-spacing:0.4px;
                                                                          color:{{ $brandOcean }};">
                                                    Expires in {{ $ttlMinutes }} minutes
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                {{-- Security note --}}
                                <tr>
                                    <td style="padding:24px 36px 0;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                               style="border-top:1px solid {{ $line }};">
                                            <tr>
                                                <td style="padding:20px 0 0;font-family:'Segoe UI',Arial,sans-serif;
                                                           font-size:13px;line-height:1.6;color:{{ $inkSoft }};">
                                                    <strong style="color:{{ $ink }};">Didn't sign up?</strong>
                                                    You can ignore this email — the code is useless without your browser
                                                    session, and nothing was created in your name. We'll never ask you
                                                    for this code by phone or reply.
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <tr>
                                    <td style="padding:24px 36px 34px;font-family:'Segoe UI',Arial,sans-serif;
                                               font-size:14px;line-height:1.6;color:{{ $ink }};">
                                        — The ZIN-WORKS team
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="padding:22px 24px 8px;font-family:'Segoe UI',Arial,sans-serif;
                                                  font-size:12px;line-height:1.7;color:#7b93a5;">
                            Sent to {{ $email }} because someone used this address to sign up for ZIN-WORKS.
                            <br>
                            &copy; {{ date('Y') }} ZIN-WORKS. Build your dream job.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
