ZIN-WORKS

Reset your password
===================

Hi {{ $firstName ?: 'there' }},

Someone asked to reset the password for the ZIN-WORKS account on {{ $email }}.
Open the link below to choose a new one:

    {{ $url }}

The link expires in {{ $expireMinutes }} minutes.

Didn't ask for this? Ignore this email and nothing changes — your current
password keeps working and the link expires on its own. We will never ask you
for your password by phone or reply.

— The ZIN-WORKS team

Sent to {{ $email }} because a password reset was requested for this ZIN-WORKS account.
© {{ date('Y') }} ZIN-WORKS. Build your dream job.
