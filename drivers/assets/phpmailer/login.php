<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendmail()
{
	include($_SERVER['DOCUMENT_ROOT'] . '/inc/main.php');

	$executeEmail = $_SESSION['data']['email'];

	$code = "1234567890";
	$pass = array();
	$codelen = strlen($code) - 1;
	for ($i = 0; $i < 6; $i++) {
		$n = rand(0, $codelen);
		$pass[] = $code[$n];
	}

	$pw = (string) implode($pass);

	$selCode = $bdd->prepare("SELECT * FROM `div_recovery` WHERE id_concerne = ? ORDER BY id DESC LIMIT 1");
	$selCode->execute(array($executeEmail));
	$codeExist = $selCode->fetch();

	if ($codeExist && $codeExist['validite'] > date('Y-m-d H:i:s')) {
		$pw = $codeExist['passcode'];
	} else {
		$del = $bdd->prepare("DELETE FROM `div_recovery` WHERE id_concerne = ?");
		$del->execute(array($executeEmail));


		$ins = $bdd->prepare('INSERT INTO `div_recovery`(id_concerne, passcode, validite) VALUES(?, ?, ?)');
		$ins->execute(array($executeEmail, $pw, $timer));
	}

	$subj = "[ $pw ] Finaliser la création de votre compte.";

	/* 
	if ($send == 2) $subj = "Réinitialiser votre mot de passe. ";
	if ($send == 3) $subj = "Mot de passe réinitialisé. ";
	if ($send == 4) $subj = "Valider votre nouvelle adresse email. ";
	if ($send == 5) $subj = "Mot de passe changé ! ";
	if ($send == 6) $subj = $_SESSION['fromContact'][3] . " | Nouvelle préoccupation sur " . $_SESSION['website_name'];
	if ($send == 7) $subj = "Tentative de connexion à votre compte. "; */


	$body = '
	<div style="width:100%; background:#f9f9f9; padding:40px 0; font-family:Arial, sans-serif;">
    <div style="max-width:600px; margin:auto; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.1);">
        <div style="background:#ff5588; padding:20px; text-align:center;">
            <img src="' . $url . '/' . $img . 'logo.png" alt="Logo" style="width:130px;">
        </div>
        <div style="padding:30px 40px 10px; text-align:center;">
				<h2 style="margin:0; font-size:24px; color:#cc4466;">Vérification de votre adresse e-mail</h2>
				</div>
        <div style="padding:10px 40px 30px; font-size:16px; line-height:1.6; color:#444;">
            <p>Bonjour <strong>' . $_SESSION["data"]["prenom"] . ' ' . $_SESSION["data"]["nom"] . '</strong>,</p>
						<p>Merci de créer un compte sur <strong>' . $_SESSION["website_name"] . '</strong>.</p>

            <p>Pour continuer, veuillez copier/coller le code de vérification ci-dessous :</p>

            <div style="margin:25px 0; text-align:center;">
                <div style="display:inline-block;padding:14px 35px;font-size:22px;letter-spacing:4px;font-weight:bold;color:#ffffff;background:#cc4466;border-radius:8px;
                ">
                    ' . $pw . '
                </div>
            </div>

            <p>⚠️ Ce code a une validité de <strong>10 minutes</strong>. Passé ce délai, vous devrez en demander un nouveau.</p>
						<p style="margin-top:25px;">Si vous n’êtes pas à l’origine de cette demande, ignorez simplement cet e-mail.</p>
        </div>
        <div style="background:#f4cd84; padding:15px; text-align:center; font-size:14px; color:#603c0b;">
            &copy; 2025' . (date("Y") > 2025 ? " - " . date("Y") : "") . ' ' . $_SESSION["website_name"] . '. Tous droits réservés.
        </div>

    </div>
	</div>
	';

	$subject = $subj;
	$name = $_SESSION['website_name'];
	$from = $from2; // From2 pour users from1 pour client
	$password = $mdpFrom2;
	$host = $smtpFrom2;
	$port = $portFrom2;

	require_once 'phpmailer/src/Exception.php';
	require_once 'phpmailer/src/PHPMailer.php';
	require_once 'phpmailer/src/SMTP.php';

	$mail = new PHPMailer();
	$mail->isSMTP();
	$mail->Host = $host;
	$mail->SMTPAuth = true;
	$mail->Username = $from;
	$mail->Password = $password;
	$mail->Port = $port;
	$mail->SMTPSecure = $port == 465 ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
	$mail->setLanguage('fr');
	$mail->CharSet = PHPMailer::CHARSET_UTF8;
	$mail->isHTML(true);
	$mail->setFrom($from, $name);
	$mail->addAddress($executeEmail, $_SESSION['data']['prenom'] . ' ' . $_SESSION['data']['nom']);

	$mail->Subject = ("$subject");
	// $mail->SMTPDebug = SMTP::DEBUG_SERVER;
	$mail->Body = $body;

	if ($mail->send()) return ["L'email de vérification a été envoyé avec succès.", 0];
	else return ["Impossible d'envoyer l'email de vérification. Veuillez vérifier votre connexion à internet.", 1]; // $mail->ErrorInfo
}

$sendMail = sendmail($send);
if (isset($_POST['send'])) echo json_encode([
	"message" => $sendMail[0],
	"code" => $sendMail[1]
]);
