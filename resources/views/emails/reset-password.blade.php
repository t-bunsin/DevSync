{{--
    Password reset link email.

    Same construction as the verification code email: table layout, inline
    styles only (Gmail and Outlook strip <style> blocks and ignore flex/grid),
    and no remote images, so a client that blocks them still gets the design.
    The button is the payload, with the raw URL underneath for the clients
    that flatten anchors.
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
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>Reset your ZIN-WORKS password</title>
    <!--[if mso]>
    <style>body,table,td,p,a{font-family:"Segoe UI",Arial,sans-serif !important;}</style>
    <![endif]-->
</head>
<body style="margin:0;padding:0;width:100%;background-color:#eef4f4;color:{{ $ink }};-webkit-font-smoothing:antialiased;">

    {{-- Preheader: the inbox preview line, hidden in the body itself. --}}
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;color:transparent;">
        Reset your ZIN-WORKS password. The link expires in {{ $expireMinutes }} minutes.
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
                                            Password recovery
                                        </p>
                                        <h1 style="margin:0;font-size:25px;line-height:1.25;font-weight:700;color:#ffffff;">
                                            Reset your password
                                        </h1>
                                    </td>
                                </tr>
                            </table>

                            {{-- Body --}}
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td style="padding:32px 36px 0;font-family:'Segoe UI',Arial,sans-serif;
                                               font-size:15px;line-height:1.6;color:{{ $inkSoft }};">
                                        <p style="margin:0 0 14px;font-size:16px;color:{{ $ink }};">
                                            Hi {{ $firstName ?: 'there' }},
                                        </p>
                                        <p style="margin:0;">
                                            Someone asked to reset the password for the ZIN-WORKS account on
                                            <strong style="color:{{ $ink }};">{{ $email }}</strong>. Choose a new one
                                            with the button below.
                                        </p>
                                    </td>
                                </tr>

                                {{-- The button. Bulletproof VML fallback so Outlook renders a real button. --}}
                                <tr>
                                    <td align="center" style="padding:28px 36px 0;">
                                        <!--[if mso]>
                                        <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml"
                                                     xmlns:w="urn:schemas-microsoft-com:office:word"
                                                     href="{{ $url }}" style="height:50px;v-text-anchor:middle;width:280px;"
                                                     arcsize="26%" stroke="f" fillcolor="{{ $brandTeal }}">
                                            <w:anchorlock/>
                                            <center style="color:#ffffff;font-family:'Segoe UI',Arial,sans-serif;font-size:15px;font-weight:700;">
                                                Choose a new password
                                            </center>
                                        </v:roundrect>
                                        <![endif]-->
                                        <!--[if !mso]><!-- -->
                                        <a href="{{ $url }}"
                                           style="display:inline-block;background-color:{{ $brandTeal }};color:#ffffff;
                                                  font-family:'Segoe UI',Arial,sans-serif;font-size:15px;font-weight:700;
                                                  line-height:50px;text-align:center;text-decoration:none;
                                                  width:280px;max-width:100%;border-radius:13px;">
                                            Choose a new password
                                        </a>
                                        <!--<![endif]-->
                                    </td>
                                </tr>

                                <tr>
                                    <td align="center" style="padding:14px 36px 0;font-family:'Segoe UI',Arial,sans-serif;
                                                              font-size:12px;font-weight:600;letter-spacing:0.4px;
                                                              color:{{ $brandOcean }};">
                                        This link expires in {{ $expireMinutes }} minutes
                                    </td>
                                </tr>

                                {{-- Fallback URL, for clients that strip the anchor. --}}
                                <tr>
                                    <td style="padding:26px 36px 0;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"
                                               style="background-color:#f2f9f8;border:1px solid #cfe7e5;border-radius:12px;">
                                            <tr>
                                                <td style="padding:14px 16px;font-family:'Segoe UI',Arial,sans-serif;
                                                           font-size:12px;line-height:1.6;color:{{ $inkSoft }};">
                                                    Button not working? Paste this into your browser:
                                                    <br>
                                                    <a href="{{ $url }}"
                                                       style="color:{{ $brandOcean }};word-break:break-all;">{{ $url }}</a>
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
                                                    <strong style="color:{{ $ink }};">Didn't ask for this?</strong>
                                                    Ignore this email and nothing changes — your current password keeps
                                                    working and the link expires on its own. We'll never ask you for your
                                                    password by phone or reply.
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
                            Sent to {{ $email }} because a password reset was requested for this ZIN-WORKS account.
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
