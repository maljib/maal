<?php // mail.php
require_once 'functions.php';
require_once '../vendor/autoload.php';

function sendMail($to, $toa, $subject, $body, $re = false, $rea = false, $atts = false, $isHTML = false) {
  $isHTML = $isHTML || preg_match('/^.*<html.*<\/html>\s*$/s', $body);
  $mail = new PHPMailer\PHPMailer\PHPMailer;
  //$mail->SMTPDebug = 3;
  $mail->CharSet = 'UTF-8';
  $mail->Host = 'smtp.gmail.com';
  $mail->Port = 587;
  $mail->isSMTP();
  $mail->SMTPSecure = 'tls';
  $mail->SMTPAuth = true;
  $mail->Username = 'maljib.org';
  $mail->Password = 'rlatjddms';

  $maljib  = '배달말집';
  $maljiba = 'maljib.org@gmail.com';
  $mail->setFrom($maljiba, $maljib);
  if (is_array($to)) {
    foreach ($to as $i => $name) {
      $mail->addAddress($toa[$i], $name);
    }
  } else {
    $mail->addAddress($toa, $to);
  }
  if ($re) {
    $mail->addReplyTo($rea, $re);
    $mail->addCC($rea, $re);
  } else {
    $mail->addReplyTo($maljiba, $maljib);
  }
  if ($atts) {
    foreach ($atts as $att) {
      $mail->addStringAttachment(base64_decode($att['data']), $att['name']);
    }
  }
  $mail->isHTML($isHTML);
  $mail->Subject = $subject;
  $mail->Body    = $body;
  $mail->send() or die("전자우편을 보낼 수 없습니다.");
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
