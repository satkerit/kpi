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
     * @param  string  $nik  NIK pegawai (ditampilkan di email).
     * @param  string  $position  Jabatan pegawai (ditampilkan di email).
     * @param  string  $email  Email pegawai (ditampilkan di email).
     * @param  string  $suid  SUID token untuk magic link.
     * @param  int  $minutes  Masa berlaku OTP dalam menit.
     */
    public function __construct(
        public string $code,
        public string $recipientName,
        public string $nik = '',
        public string $position = '',
        public string $email = '',
        public string $suid = '',
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
     *
     * Desain OTP: 1 digit per kotak (6 kotak), tanpa spasi antar digit.
     */
    private function renderHtml(): string
    {
        $appName = e(AppSetting::get('app_name', config('app.name', 'KPI 360')));

        $name = trim($this->recipientName);
        $firstName = e(explode(' ', $name)[0] ?: $name);

        // Pecah kode OTP digit-per-digit untuk kotak individual.
        $digits = str_split($this->code);
        $otpBoxes = '';
        foreach ($digits as $d) {
            $otpBoxes .= '<td style="padding:0 4px;">'
                .'<div style="display:inline-block; width:56px; height:64px; line-height:64px; text-align:center;'
                .' background:#ffffff; border:2px solid #b98e1f; border-radius:8px;'
                .' font-family:\'JetBrains Mono\', \'Courier New\', monospace; font-size:30px; font-weight:700; color:#0f2438;">'
                .e($d)
                .'</div></td>';
        }

        $expires = e(now()->addMinutes($this->minutes)->translatedFormat('d F Y \pukul H:i'));

        $font = "'Segoe UI', 'Helvetica Neue', Arial, sans-serif";

        // Info employee (NIK, nama, jabatan, email).
        $nik = e($this->nik);
        $fullName = e($this->recipientName);
        $position = e($this->position);
        $email = e($this->email);

        // Magic link: SUID token embedded di URL.
        // Link ini one-time use — setelah dipakai, token di-consume dari cache.
        $magicLink = e(route('questionnaire.verify.link', [
            'nik' => $this->nik,
            'suid' => $this->suid,
        ]));

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
            .'          </p>'
            // Info Employee
            .'          <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8f9fb; border-radius:8px; border:1px solid #e5e9f0; margin:0 0 24px 0;">'
            .'            <tr><td style="padding:16px 20px; font-family:'.$font.'; font-size:13px; color:#3d4c63;">'
            .'              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">'
            .'                <tr>'
            .'                  <td style="padding:4px 0; color:#7c8aa0; width:100px;">NIK</td>'
            .'                  <td style="padding:4px 0; color:#16233a; font-weight:600;">'.$nik.'</td>'
            .'                </tr>'
            .'                <tr>'
            .'                  <td style="padding:4px 0; color:#7c8aa0;">Nama</td>'
            .'                  <td style="padding:4px 0; color:#16233a; font-weight:600;">'.$fullName.'</td>'
            .'                </tr>'
            .'                <tr>'
            .'                  <td style="padding:4px 0; color:#7c8aa0;">Jabatan</td>'
            .'                  <td style="padding:4px 0; color:#16233a; font-weight:600;">'.$position.'</td>'
            .'                </tr>'
            .'                <tr>'
            .'                  <td style="padding:4px 0; color:#7c8aa0;">Email</td>'
            .'                  <td style="padding:4px 0; color:#16233a; font-weight:600;">'.$email.'</td>'
            .'                </tr>'
            .'              </table>'
            .'            </td></tr>'
            .'          </table>'
            // Magic Link (primary CTA)
            .'          <p style="margin:0 0 16px 0;">'
            .'            <strong style="color:#16233a;">Cara tercepat:</strong> klik tombol di bawah untuk langsung masuk ke formulir kuesioner — tanpa perlu memasukkan kode.'
            .'          </p>'
            .'          <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px 0;">'
            .'            <tr><td align="center">'
            .'              <a href="'.$magicLink.'" style="display:inline-block; background:linear-gradient(135deg, #0f2438 0%, #16324f 100%); color:#ffffff; font-family:'.$font.'; font-size:15px; font-weight:700; padding:14px 32px; border-radius:8px; text-decoration:none; letter-spacing:0.3px;">'
            .'                &#10148; &nbsp;Verifikasi &amp; Buka Formulir KPI'
            .'              </a>'
            .'            </td></tr>'
            .'          </table>'
            .'          <p style="margin:0 0 24px 0; font-size:12px; color:#7c8aa0; text-align:center;">'
            .'            Jika tombol di atas tidak berfungsi, salin tautan berikut ke browser:'
            .'          </p>'
            .'          <p style="margin:0 0 24px 0; font-size:11px; color:#7c8aa0; word-break:break-all; font-family:\'JetBrains Mono\', \'Courier New\', monospace;">'.$magicLink.'</p>'
            // Alternatif: input manual
            .'          <div style="border-top:1px solid #e5e9f0; margin:0 0 20px 0;"></div>'
            .'          <p style="margin:0 0 16px 0;">'
            .'            <strong style="color:#16233a;">Atau masukkan kode manual:</strong>'
            .'          </p>'
            // Kode OTP — 6 kotak individual (1 digit per kotak)
            .'          <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 auto 24px auto;">'
            .'            <tr><td align="center" style="background-color:#f6ecd4; border-radius:12px; padding:24px 20px;">'
            .'              <table role="presentation" cellpadding="0" cellspacing="0" align="center">'
            .'                <tr>'
            .$otpBoxes
            .'                </tr>'
            .'              </table>'
            .'            </td></tr>'
            .'          </table>'
            // Masa berlaku
            .'          <p style="margin:0 0 24px 0; font-size:13px; color:#7c8aa0; text-align:center;">'
            .'            Kode dan tautan ini berlaku hingga <strong style="color:#3d4c63;">'.$expires.'</strong>.'
            .'          </p>'
            // Pemisah
            .'          <div style="border-top:1px solid #e5e9f0; margin:0 0 20px 0;"></div>'
            // Warning
            .'          <p style="margin:0 0 8px 0; font-size:13px; color:#7c8aa0;">'
            .'            &#9888;&#65039; Jika Anda tidak meminta kode ini, abaikan email &#8212; tidak ada tindakan yang perlu dilakukan.'
            .'          </p>'
            .'          <p style="margin:0; font-size:13px; color:#7c8aa0;">'
            .'            Demi keamanan, jangan bagikan kode atau tautan ini kepada siapa pun, termasuk tim IT atau atasan.'
            .'            Tim '.$appName.' tidak akan pernah meminta kode atau tautan verifikasi ini.'
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
