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

	$del = $bdd->prepare("DELETE FROM `div_recovery` WHERE id_concerne = ?");
	$del->execute(array($executeEmail));


	$ins = $bdd->prepare('INSERT INTO `div_recovery`(id_concerne, passcode, validite) VALUES(?, ?, ?)');
	$ins->execute(array($executeEmail, $pw, $timer5min)); // +5 minutes

	$subj = "[ $pw ] Réinitialiser votre mot de passe. ";

	$body = '
	<div style="width:100%; background:#f9f9f9; padding:40px 0; font-family:Arial, sans-serif;">
    <div style="max-width:600px; margin:auto; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.1);">
        <div style="background:#ff5588; padding:20px; text-align:center;">
            <img src="' . $url . '/' . $img . 'logo.png" alt="Logo" style="width:130px;">
        </div>
        <div style="padding:30px 40px 10px; text-align:center;">
				<h2 style="margin:0; font-size:24px; color:#cc4466;">Réinitialisation du mot de passe.</h2>
				</div>
        <div style="padding:10px 40px 30px; font-size:16px; line-height:1.6; color:#444;">
            <p><strong>' . $_SESSION["data"]["prenom"] . ' ' . $_SESSION["data"]["nom"] . '</strong>, vous avez fait une demande de réinitialisation de votre mot de passe <b>par email</b> sur le site <strong>' . $_SESSION["website_name"] . '</strong>.</p>
						<p>Afin de nous assurer que c\'est bien vous qui êtes à l\'origine de cette demande, veuillez copier/coller le code de vérification ci-dessous :</p>

            <div style="margin:25px 0; text-align:center;">
                <div style="
                    display:inline-block;
                    padding:14px 35px;
                    font-size:22px;
                    letter-spacing:4px;
                    font-weight:bold;
                    color:#ffffff;
                    background:#cc4466;
                    border-radius:8px;
                ">
                    ' . $pw . '
                </div>
            </div>

            <p>Ce code a une validité de <strong>5 minutes</strong>. Passé ce délai, vous devrez en demander un nouveau.</p>
						<p style="margin-top:25px;">Si vous n’êtes pas à l’origine de cette demande, veuillez ignorez cet e-mail.</p>
        </div>
        <div style="background:#f4cd84; padding:15px; text-align:center; font-size:14px; color:#603c0b;">
            &copy; 2025' . (date("Y") > 2025 ? " - " . date("Y") : "") . ' ' . $_SESSION["website_name"] . '. Tous droits réservés.
        </div>

    </div>
	</div>
	';

	$subject = $subj;
	$name = $_SESSION['website_name'];
	$from = $from2;
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
	$mail->addAddress($executeEmail, isset($_SESSION['data']['nom']) ? $_SESSION['data']['prenom'] . ' ' . $_SESSION['data']['nom'] : $_SESSION['data']['pseudo']);

	$mail->Subject = ("$subject");
	// $mail->SMTPDebug = SMTP::DEBUG_SERVER;
	$mail->Body = $body;

	if ($mail->send()) return ["L'email de vérification a été envoyé avec succès.", 0];
	else return [$mail->ErrorInfo/*"Impossible d'envoyer l'email de vérification. Veuillez vérifier votre connexion à internet."*/, 1]; // $mail->ErrorInfo
}

$sendMail = sendmail();
if (isset($_POST['send'])) echo json_encode([
	"message" => $sendMail[0],
	"code" => $sendMail[1]
]);
