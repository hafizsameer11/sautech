<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmailWithAttachment($to, $toName, $fromName, $csvContent)
{
    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        // Prepare headers
        $subject = 'Reseller Commission Report';
        $message = "Hello $toName,<br><br>Please find attached your commission report.<br><br>Regards,<br>$fromName";

        // Generate a boundary string
        $separator = md5(time());
        $eol = PHP_EOL;

        // Main headers
        $headers = "From: $fromName <Support@sautech.net>$eol";
        $headers .= "MIME-Version: 1.0$eol";
        $headers .= "Content-Type: multipart/mixed; boundary=\"$separator\"$eol";

        // Message Body
        $body = "--$separator$eol";
        $body .= "Content-Type: text/html; charset=\"UTF-8\"$eol";
        $body .= "Content-Transfer-Encoding: 7bit$eol$eol";
        $body .= $message . "$eol";

        // Attachment
        $attachment = chunk_split(base64_encode($csvContent));
        $body .= "--$separator$eol";
        $body .= "Content-Type: text/csv; name=\"reseller_commission.csv\"$eol";
        $body .= "Content-Transfer-Encoding: base64$eol";
        $body .= "Content-Disposition: attachment; filename=\"reseller_commission.csv\"$eol$eol";
        $body .= $attachment . "$eol";
        $body .= "--$separator--";

        // Send mail
        if (mail($to, $subject, $body, $headers)) {
            return true;
        } else {
            return "Mail sending failed.";
        }
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}
function sendQuote($to, $toName, $fromName ,$csvContent)
{
    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host = 'ironfoot.onlinehosting.co.za'; // ✅ custom SMTP server
        $mail->SMTPAuth = false;                       // ✅ no authentication needed
        $mail->Port = 25;                              // ✅ port 25 (non-encrypted)
        $mail->SMTPSecure = false;

        // From and to
        $mail->setFrom('Support@sautech.net', $fromName);
        $mail->addAddress($to, $toName);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Sautech Quote';
        $mail->Body = "Hello $toName,<br><br>Please find attached your quote.<br><br>Regards,<br>$fromName";

        // Attach CSV as a string
        $mail->addStringAttachment($csvContent, 'reseller_commission.csv', 'base64', 'text/csv');

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}
