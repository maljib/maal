<?php // mail.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once 'functions.php';
require_once '../vendor/autoload.php';
Dotenv\Dotenv::createImmutable(__DIR__ . "/..")->load();
function sendMail($to, $to_addr, $subject, $body, $re = false, $re_addr = false, $atts = false, $isHTML = false) {
  try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->CharSet    = PHPMailer::CHARSET_UTF8;
    $mail->Port       = 587;
    $mail->SMTPAuth   = true;
    $mail->Host       = 'smtp.gmail.com';
    $mail->Username   = $_ENV['MAIL_USER'];
    $mail->Password   = $_ENV['MAIL_PASS'];
    // $mail->SMTPDebug = 3;
    $maljib  = '배달말집';
    $mail->setFrom($mail->Username, $maljib);
    if (is_array($to)) {
      foreach ($to as $i => $name) {
        $mail->addAddress($to_addr[$i], $name);
      }
    } else {
      $mail->addAddress($to_addr, $to);
    }
    if ($re) {
      $mail->addReplyTo($re_addr, $re);
      $mail->addCC($re_addr, $re);
    } else {
      $mail->addReplyTo($mail->Username, $maljib);
    }
    if ($atts) {
      foreach ($atts as $att) {
        $mail->addStringAttachment(base64_decode($att['data']), $att['name']);
      }
    }
    $isHTML = $isHTML || preg_match('/^.*<html.*<\/html>\s*$/s', $body);
    $mail->isHTML($isHTML);
    $mail->Subject = $subject;
    $mail->Body    = $body;
    $mail->send();
  } catch (Exception $e) {
    die("전자우편을 보낼 수 없습니다. 오류: {$mail->ErrorInfo}");
  }
}

function sendMail4($nick, $mail, $subject, $text) {
  if (!$mail) {
    $mail = mess(selectValue("SELECT mail FROM users WHERE nick='".escapeString($nick)."'"))
          or die("아이디($nick)가 없습니다.");
  }
  sendMail($nick, $mail, $subject, "$nick 님께,\n\n$text");
}

function sendMail3($id, $subject, $text) {
  $row = selectRow("SELECT nick, mail FROM users WHERE id = $id")
            or die("사용자 번호($id)가 없습니다.");
  sendMail4($row[0], mess($row[1]), $subject, $text);
}
?>
