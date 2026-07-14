{{-- Customer survey invitation email. Renders only the neutral subject and the opaque
     survey link; Blade auto-escapes all output. No PII, medical, or transaction content. --}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subjectLine }}</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; color: #1a1a1a; background: #f5f5f5; margin: 0; padding: 24px;">
    <div style="max-width: 480px; margin: 0 auto; background: #ffffff; border-radius: 8px; padding: 32px;">
        <h1 style="font-size: 18px; margin: 0 0 16px;">{{ $subjectLine }}</h1>
        <p style="font-size: 14px; line-height: 1.6; margin: 0 0 24px;">
            {{ __('Kami sangat menghargai masukan Anda. Survei ini singkat dan anonim.') }}
        </p>
        <p style="margin: 0 0 24px;">
            <a href="{{ $url }}" style="display: inline-block; background: #0b5; color: #ffffff; text-decoration: none; padding: 12px 20px; border-radius: 6px; font-size: 14px;">
                {{ __('Isi Survei') }}
            </a>
        </p>
        <p style="font-size: 12px; color: #666; margin: 0;">
            {{ __('Jika tombol tidak berfungsi, salin tautan ini ke peramban Anda:') }}<br>
            <span style="word-break: break-all;">{{ $url }}</span>
        </p>
    </div>
</body>
</html>
