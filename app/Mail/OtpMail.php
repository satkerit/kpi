<?php

namespace App\Mail;

use App\Models\AppSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email verifikasi OTP kuesioner.
 *
 * Disengaja:
 *  - Subjek JUMPA umum (tanpa nama pegawai) → mencegah spam-folder heuristik.
 *  - Isi HTML email dengan inline CSS agar render konsisten di Gmail,
 *    Outlook, Apple Mail, dan client korporat lainnya.
 *  - Kode OTP dipisahkan menjadi 2 blok digit (XXX XXX) untuk keterbacaan.
 */
final class OtpMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    /**
     * @param  string  $code  Kode OTP 6 digit.
     * @param  string  $recipientName  Nama penerima (sapaan personal).
     * @param  int  $minutes  Masa berlaku OTP dalam menit.
     */
    public function __construct(
        public string $code,
        public string $recipientName,
        public int $minutes = 10,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kode Verifikasi Kuesioner KPI',
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: $this->renderHtml(),
        );
    }

    /**
     * Render HTML email dengan inline CSS (dipilih inline karena sebagian
     * client email — Gmail, Outlook — memblokir <style> di <head>).
     */
    private function renderHtml(): string
    {
        $appName = e(AppSetting::get('app_name', config('app.name', 'KPI 360')));

        $name = trim($this->recipientName);
        $firstName = e(explode(' ', $name)[0] ?: $name);

        $code = $this->code;
        $left = e(substr($code, 0, 3));
        $right = e(substr($code, 3, 3));

        $expires = e(now()->addMinutes($this->minutes)->translatedFormat('d F Y \pukul H:i'));

        $font = "'Segoe UI', 'Helvetica Neue', Arial, sans-serif";
        $mono = "'JetBrains Mono', 'Courier New', monospace";

        return '<!DOCTYPE html>'
            .'<html lang="id">'
            .'<head>'
            .'<meta charset="UTF-8">'
            .'<meta name="viewport" content="width=device-width, initial-scale=1.0">'
            .'<title>Kode Verifikasi Kuesioner KPI</title>'
            .'</head>'
            .'<body style="margin:0; padding:0; background-color:#f4f5f7; -webkit-text-size-adjust:100%;">'
            .'  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f5f7; padding:24px 0;">'
            .'    <tr><td align="center">'
            .'      <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background-color:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 1px 3px rgba(15,36,56,0.08);">'
            // Header
            .'        <tr><td style="background:linear-gradient(135deg, #0f2438 0%, #16324f 100%); padding:32px 40px 28px 40px; text-align:center;">'
            .'          <div style="display:inline-block; background:#b98e1f; color:#ffffff; font-family:'.$font.'; font-size:11px; font-weight:700; letter-spacing:1.5px; padding:4px 10px; border-radius:4px; text-transform:uppercase;">Verifikasi</div>'
            .'          <div style="margin-top:14px; font-family:'.$font.'; font-size:18px; font-weight:700; color:#ffffff; letter-spacing:0.2px;">'.$appName.'</div>'
            .'        </td></tr>'
            // Body
            .'        <tr><td style="padding:36px 40px 12px 40px; font-family:'.$font.'; color:#3d4c63; font-size:15px; line-height:1.6;">'
            .'          <p style="margin:0 0 8px 0; color:#16233a; font-size:16px; font-weight:600;">Halo, '.$firstName.'</p>'
            .'          <p style="margin:0 0 20px 0;">'
            .'            Kami telah menerima permintaan verifikasi untuk pengisian <strong style="color:#16233a;">Kuesioner KPI</strong> di '.$appName.'.'
            .'            Masukkan kode di bawah ini ke dalam formulir:'
            .'          </p>'
            // Kode OTP
            .'          <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px 0;">'
            .'            <tr><td align="center" style="background-color:#f6ecd4; border-radius:10px; padding:20px 24px;">'
            .'              <div style="font-family:'.$mono.'; font-size:34px; font-weight:700; letter-spacing:8px; color:#8a6a12; line-height:1;">'.$left.'&nbsp;&nbsp;'.$right.'</div>'
            .'            </td></tr>'
            .'          </table>'
            // Masa berlaku
            .'          <p style="margin:0 0 24px 0; font-size:13px; color:#7c8aa0; text-align:center;">'
            .'            Kode ini berlaku hingga <strong style="color:#3d4c63;">'.$expires.'</strong>.'
            .'          </p>'
            // Pemisah
            .'          <div style="border-top:1px solid #e5e9f0; margin:0 0 20px 0;"></div>'
            // Warning
            .'          <p style="margin:0 0 8px 0; font-size:13px; color:#7c8aa0;">'
            .'            &#9888;&#65039; Jika Anda tidak meminta kode ini, abaikan email &#8212; tidak ada tindakan yang perlu dilakukan.'
            .'          </p>'
            .'          <p style="margin:0; font-size:13px; color:#7c8aa0;">'
            .'            Demi keamanan, jangan bagikan kode ini kepada siapa pun, termasuk tim IT atau atasan.'
            .'            Tim '.$appName.' tidak akan pernah meminta kode ini.'
            .'          </p>'
            .'        </td></tr>'
            // Footer
            .'        <tr><td style="background-color:#f8f9fb; padding:20px 40px; text-align:center; border-top:1px solid #e5e9f0;">'
            .'          <p style="margin:0; font-family:'.$font.'; font-size:12px; color:#7c8aa0; line-height:1.6;">'
            .'            Email ini dikirim otomatis oleh '.$appName.'.<br>'
            .'            Jika Anda memiliki pertanyaan, silakan hubungi unit SDM.'
            .'          </p>'
            .'        </td></tr>'
            .'      </table>'
            .'    </td></tr>'
            .'  </table>'
            .'</body>'
            .'</html>';
    }
}
