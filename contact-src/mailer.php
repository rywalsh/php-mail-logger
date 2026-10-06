<?php
class Mailer {
    private static function header($s) {
        $s = preg_replace('/[\r\n]+/', ' ', (string) $s);
        return preg_match('/[^\x20-\x7e]/', $s) ? '=?UTF-8?B?' . base64_encode($s) . '?=' : $s;
    }

    public static function sendContact($to, $name, $email, $subject, $message, $pageUrl, $ip) {
        $subjectLine = '[Contact] ' . ($subject !== '' ? $subject : "Message from $name");
        $safeName = str_replace(['"', '\\'], '', preg_replace('/[\r\n]+/', ' ', $name));
        $replyTo = '"' . self::header($safeName) . '" <' . $email . '>';

        $text = "New contact form message\n\n"
            . "Name:    $name\nEmail:   $email\n"
            . ($subject !== '' ? "Subject: $subject\n" : '')
            . ($pageUrl !== '' ? "Page:    $pageUrl\n" : '')
            . "\n$message\n\n--\nReply to this email to respond to $name.\n";

        $c = (new Settings)->colors();
        $row = fn($label, $value) => '<tr><td style="padding:6px 12px 6px 0;color:#6b7280;white-space:nowrap;vertical-align:top">'
            . $label . '</td><td style="padding:6px 0;color:#111827">' . $value . '</td></tr>';
        $rows = $row('Name', e($name))
            . $row('Email', '<a href="mailto:' . e($email) . '" style="color:' . $c['color_primary'] . '">' . e($email) . '</a>')
            . ($subject !== '' ? $row('Subject', e($subject)) : '')
            . ($pageUrl !== '' ? $row('Page', e($pageUrl)) : '');
        $html = '<!doctype html><html><body style="margin:0;padding:24px;background:#f3f4f6;font-family:-apple-system,Segoe UI,Helvetica,Arial,sans-serif">'
            . '<div style="max-width:600px;margin:0 auto;background:#ffffff;border-radius:8px;overflow:hidden;border:1px solid #e5e7eb">'
            . '<div style="background:' . $c['color_primary'] . ';color:' . $c['color_button_text'] . ';padding:16px 24px;font-size:18px;font-weight:600">New contact form message</div>'
            . '<div style="padding:24px"><table style="border-collapse:collapse;font-size:15px">' . $rows . '</table>'
            . '<div style="margin:20px 0 0;padding:16px;background:#f9fafb;border-left:4px solid ' . $c['color_primary'] . ';border-radius:4px;font-size:15px;line-height:1.5;color:#111827;white-space:pre-wrap">'
            . e($message) . '</div>'
            . '<p style="margin:20px 0 0;font-size:13px;color:#6b7280">Hit reply to respond to ' . e($name) . ' directly.</p></div></div></body></html>';

        $boundary = 'b_' . bin2hex(random_bytes(12));
        $body = "--$boundary\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($text))
            . "--$boundary\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: base64\r\n\r\n"
            . chunk_split(base64_encode($html))
            . "--$boundary--";
        $headers = implode("\r\n", [
            'From: ' . Config::get('mail_from'),
            'Reply-To: ' . $replyTo,
            'MIME-Version: 1.0',
            "Content-Type: multipart/alternative; boundary=\"$boundary\"",
        ]);

        return @mail($to, self::header($subjectLine), $body, $headers);
    }
}
