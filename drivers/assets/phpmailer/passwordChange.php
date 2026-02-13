<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

function sendmail($code = '')
{
	include($_SERVER['DOCUMENT_ROOT'] . '/inc/main.php');

	$executeEmail = $_SESSION['data']['email'];
	$prenom = $_SESSION['data']['prenom'];
	$nom = $_SESSION['data']['nom'];

	$subject = "Confirmation du changement de mot de passe";

	$body = '
  <div style="width:100%; background:#f9f9f9; padding:40px 0; font-family:Arial, sans-serif;">
    <div style="max-width:600px; margin:auto; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.1);">
        <div style="background:#ff5588; padding:20px; text-align:center;">
            <img src="' . $url . '/' . $img . 'logo.png" alt="Logo" style="width:130px;">
        </div>
        <div style="padding:30px 40px 10px; text-align:center;">
        <h2 style="margin:0; font-size:24px; color:#cc4466;">Mot de passe modifié avec succès.</h2>
        </div>
        <div style="padding:10px 40px 30px; font-size:16px; line-height:1.6; color:#444;">
            <p><strong>' . htmlspecialchars($prenom . ' ' . $nom) . '</strong>, nous confirmons que le mot de passe de votre compte sur <strong>' . $_SESSION["website_name"] . '</strong> a été modifié avec succès.</p>
            
            <p>Cette modification a été effectuée suite à votre demande de ' . ($code == '' ? 'réinitialisation' : 'changement du mot de passe via les Paramètres de votre compte') . '.</p>

            <div style="margin:25px 0; padding:15px; background:#e8f5e9; border-left:4px solid #4caf50; border-radius:4px;">
                <p style="margin:0; color:#2e7d32;"><strong>✓ Modification confirmée</strong></p>
                <p style="margin:5px 0 0 0; font-size:14px; color:#558b2f;">Vous pouvez à présent vous connecter ou effectuer d\'autres actions avec votre nouveau mot de passe.</p>
            </div>

            <p style="margin-top:25px;"><strong>⚠️ Important :</strong> Si vous n\'êtes pas à l\'origine de cette demande, alors, veuillez <a href="' . $url . '/logout/" style="color:#ff5588; font-weight:bold;">Réinitialiser votre mot de passe immédiatement</a>.</p>

            <p><strong>Conseils de sécurité :</strong></p>
            <ul style="color:#666;">
              <li>Utilisez un mot de passe unique et complexe (minimum 8 caractères)</li>
              <li>Ne partagez jamais vos identifiants</li>
              <li>Vérifiez l\'activité de votre compte régulièrement</li>
              <li>Activez l\'authentification à deux facteurs (double authentification).</li>
            </ul>
        </div>
        <div style="background:#f4cd84; padding:15px; text-align:center; font-size:14px; color:#603c0b;">
            &copy; 2025' . (date("Y") > 2025 ? " - " . date("Y") : "") . ' ' . $_SESSION["website_name"] . '. Tous droits réservés.
        </div>

    </div>
  </div>
  ';

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

	$mail->Subject = $subject;
	$mail->Body = $body;
	$mail->Debugoutput;

	if ($mail->send()) return ["Le mot de passe a été ". ($code == '' ? 'réinitialisé' : 'modifié')." et un email de confirmation a été envoyé.", 0];
	else return [$mail->ErrorInfo /* "Une erreur s'est produite lors du changement de votre mot de passe. Veuillez vérifier votre connexion à internet puis rééssayer." */, 1];
}

$sendMail = sendmail(isset($newPass) ? 1 : '');
?>
