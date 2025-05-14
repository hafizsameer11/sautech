<?php 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmailWithAttachment($to, $toName, $fromName, $csvContent) {
    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  // or your SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = 'www.hamzaranar@gmail.com';       // your email
        $mail->Password = 'htoiieailezgnzlf';          // app-specific password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // From and to
        $mail->setFrom('www.hamzaranar@gmail.com', $fromName);
        $mail->addAddress($to, $toName);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Reseller Commission Report';
        $mail->Body = "Hello $toName,<br><br>Please find attached your commission report.<br><br>Regards,<br>$fromName";

        // Attach CSV as a string
        $mail->addStringAttachment($csvContent, 'reseller_commission.csv', 'base64', 'text/csv');

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}
function sendSimpleEmail($to, $toName, $fromName, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';  // or your SMTP host
        $mail->SMTPAuth = true;
        $mail->Username = 'www.hamzaranar@gmail.com';       // your email
        $mail->Password = 'htoiieailezgnzlf';          // app-specific password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // From and to
        $mail->setFrom('www.hamzaranar@gmail.com', $fromName);
        $mail->addAddress($to, $toName);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}
